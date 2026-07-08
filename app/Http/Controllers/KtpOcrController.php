<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class KtpOcrController extends Controller
{
    public function openai(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:png,jpg,jpeg,webp|max:8192',
        ]);

        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            return response()->json([
                'message' => 'OPENAI_API_KEY belum dikonfigurasi di server.',
            ], 500);
        }

        $file = $request->file('image');
        $base64 = base64_encode(file_get_contents($file->getRealPath()));
        $mime = $file->getMimeType();
        $dataUrl = "data:{$mime};base64,{$base64}";

        $instruction = 'Anda adalah mesin ekstraksi data KTP Indonesia. '
            . 'Baca gambar KTP dan kembalikan HANYA JSON valid tanpa teks lain dengan struktur: '
            . '{"nik":"","name":"","gender":"","birth_place":"","birth_date":"","address":"","rt_rw":"","kelurahan":"","kecamatan":""}. '
            . 'Aturan: nik hanya 16 digit angka; gender bernilai "Laki-Laki" atau "Perempuan"; '
            . 'birth_date format YYYY-MM-DD; rt_rw format contoh "001/002"; '
            . 'kosongkan string jika tidak terbaca. Jangan menambahkan field lain.';

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'temperature' => 0,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $instruction],
                                ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
                            ],
                        ],
                    ],
                ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menghubungi OpenAI: ' . $e->getMessage()], 502);
        }

        if ($response->failed()) {
            return response()->json([
                'message' => 'OpenAI error: ' . $response->json('error.message', 'unknown'),
            ], $response->status());
        }

        $content = $response->json('choices.0.message.content');
        $data = json_decode($content, true);
        if (!is_array($data)) {
            return response()->json(['message' => 'Respons OpenAI tidak valid.'], 422);
        }

        $keys = ['nik', 'name', 'gender', 'birth_place', 'birth_date', 'address', 'rt_rw', 'kelurahan', 'kecamatan'];
        $mapped = [];
        foreach ($keys as $key) {
            $mapped[$key] = isset($data[$key]) ? trim((string) $data[$key]) : '';
        }
        $mapped['nik'] = preg_replace('/\D/', '', $mapped['nik']);

        return response()->json(['mapped' => $mapped]);
    }

    public function paddle(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:png,jpg,jpeg,webp|max:8192',
        ]);

        $baseUrl = config('services.paddleocr.url');
        if (empty($baseUrl)) {
            return response()->json([
                'message' => 'PADDLEOCR_URL belum dikonfigurasi di server.',
            ], 500);
        }

        $file = $request->file('image');

        try {
            $http = Http::timeout(config('services.paddleocr.timeout', 60));

            if ($token = config('services.paddleocr.token')) {
                $http = $http->withToken($token);
            }

            $response = $http
                ->attach('image', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post(rtrim($baseUrl, '/'));
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menghubungi PaddleOCR: ' . $e->getMessage()], 502);
        }

        if ($response->failed()) {
            return response()->json([
                'message' => 'PaddleOCR error: ' . $response->json('message', 'unknown'),
            ], $response->status());
        }

        $json = $response->json();

        // Dukung dua bentuk respons: sudah termapping, atau berupa baris teks mentah.
        if (isset($json['mapped']) && is_array($json['mapped'])) {
            $mapped = $this->normalizeMapped($json['mapped']);
            return response()->json(['mapped' => $mapped, 'raw' => $json['raw'] ?? null]);
        }

        $lines = $this->extractLines($json);
        if (empty($lines)) {
            return response()->json(['message' => 'PaddleOCR tidak mengembalikan teks yang dapat dibaca.'], 422);
        }

        $mapped = $this->parseKtpLines($lines);

        return response()->json([
            'mapped' => $mapped,
            'raw' => implode("\n", $lines),
        ]);
    }

    private function extractLines($json): array
    {
        if (is_string($json)) {
            return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $json))));
        }

        if (!is_array($json)) {
            return [];
        }

        // Bentuk umum: {"text": "..."} atau {"lines": ["...", "..."]} atau {"result": [...]}
        if (isset($json['text']) && is_string($json['text'])) {
            return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $json['text']))));
        }

        $candidates = $json['lines'] ?? $json['result'] ?? $json['results'] ?? $json['data'] ?? $json;
        $lines = [];

        foreach ((array) $candidates as $item) {
            if (is_string($item)) {
                $line = trim($item);
                if ($line !== '') {
                    $lines[] = $line;
                }
            } elseif (is_array($item)) {
                $text = $item['text'] ?? $item['transcription'] ?? $item['label'] ?? null;
                if (is_string($text) && trim($text) !== '') {
                    $lines[] = trim($text);
                }
            }
        }

        return $lines;
    }

    private function parseKtpLines(array $lines): array
    {
        $mapped = [
            'nik' => '', 'name' => '', 'gender' => '', 'birth_place' => '',
            'birth_date' => '', 'address' => '', 'rt_rw' => '',
            'kelurahan' => '', 'kecamatan' => '',
        ];

        $joined = implode("\n", $lines);

        // NIK: 16 digit angka
        if (preg_match('/\b(\d{16})\b/', preg_replace('/\s+/', '', $joined), $m)) {
            $mapped['nik'] = $m[1];
        } else {
            foreach ($lines as $line) {
                $digits = preg_replace('/\D/', '', $line);
                if (strlen($digits) === 16) {
                    $mapped['nik'] = $digits;
                    break;
                }
            }
        }

        foreach ($lines as $i => $line) {
            $upper = strtoupper($line);

            // Nama
            if ($mapped['name'] === '' && str_contains($upper, 'NAMA')) {
                $mapped['name'] = $this->afterLabel($line, ['Nama']) ?: ($lines[$i + 1] ?? '');
            }

            // Tempat/Tgl Lahir
            if ($mapped['birth_date'] === '' && str_contains($upper, 'LAHIR')) {
                $val = $this->afterLabel($line, ['Tempat/Tgl Lahir', 'Tempat/Tgi Lahir', 'Tempat Lahir', 'Lahir']) ?: ($lines[$i + 1] ?? '');
                if ($val !== '') {
                    if (preg_match('/^(.*?)[,]?\s*(\d{2}[-\/]\d{2}[-\/]\d{4})/u', $val, $mm)) {
                        $mapped['birth_place'] = trim($mm[1]) ?: $mapped['birth_place'];
                        $mapped['birth_date'] = $this->normalizeDate($mm[2]);
                    } elseif ($mapped['birth_place'] === '') {
                        $mapped['birth_place'] = trim($val);
                    }
                }
            }

            // Jenis Kelamin
            if ($mapped['gender'] === '' && (str_contains($upper, 'KELAMIN') || str_contains($upper, 'JENIS'))) {
                if (str_contains($upper, 'PEREMPUAN')) {
                    $mapped['gender'] = 'Perempuan';
                } elseif (str_contains($upper, 'LAKI')) {
                    $mapped['gender'] = 'Laki-Laki';
                }
            }
            if ($mapped['gender'] === '') {
                if (str_contains($upper, 'PEREMPUAN')) {
                    $mapped['gender'] = 'Perempuan';
                } elseif (str_contains($upper, 'LAKI-LAKI') || str_contains($upper, 'LAKI LAKI')) {
                    $mapped['gender'] = 'Laki-Laki';
                }
            }

            // Alamat
            if ($mapped['address'] === '' && str_starts_with($upper, 'ALAMAT')) {
                $mapped['address'] = $this->afterLabel($line, ['Alamat']) ?: ($lines[$i + 1] ?? '');
            }

            // RT/RW
            if ($mapped['rt_rw'] === '' && (str_contains($upper, 'RT/RW') || str_contains($upper, 'RT / RW') || str_contains($upper, 'RTRW'))) {
                if (preg_match('/(\d{1,3})\s*[\/-]\s*(\d{1,3})/', $line, $mm)) {
                    $mapped['rt_rw'] = str_pad($mm[1], 3, '0', STR_PAD_LEFT) . '/' . str_pad($mm[2], 3, '0', STR_PAD_LEFT);
                }
            }

            // Kelurahan / Desa
            if ($mapped['kelurahan'] === '' && (str_contains($upper, 'KEL/DESA') || str_contains($upper, 'KEL / DESA') || str_contains($upper, 'KELURAHAN') || str_contains($upper, 'DESA'))) {
                $mapped['kelurahan'] = $this->afterLabel($line, ['Kel/Desa', 'Kelurahan', 'Desa']);
            }

            // Kecamatan
            if ($mapped['kecamatan'] === '' && str_contains($upper, 'KECAMATAN')) {
                $mapped['kecamatan'] = $this->afterLabel($line, ['Kecamatan']);
            }
        }

        // RT/RW global fallback
        if ($mapped['rt_rw'] === '' && preg_match('/\b(\d{2,3})\s*[\/]\s*(\d{2,3})\b/', $joined, $mm)) {
            $mapped['rt_rw'] = $mm[1] . '/' . $mm[2];
        }

        return $this->normalizeMapped($mapped);
    }

    private function afterLabel(string $line, array $labels): string
    {
        // Buang label lalu ambil bagian setelah pemisah ":" jika ada.
        $value = $line;
        foreach ($labels as $label) {
            $value = preg_replace('/^\s*' . preg_quote($label, '/') . '\s*/i', '', $value);
        }
        if (str_contains($value, ':')) {
            $value = substr($value, strpos($value, ':') + 1);
        }
        return trim($value, " \t\n\r\0\x0B:-");
    }

    private function normalizeDate(string $date): string
    {
        $date = str_replace('/', '-', trim($date));
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return $date;
    }

    private function normalizeMapped(array $data): array
    {
        $keys = ['nik', 'name', 'gender', 'birth_place', 'birth_date', 'address', 'rt_rw', 'kelurahan', 'kecamatan'];
        $mapped = [];
        foreach ($keys as $key) {
            $mapped[$key] = isset($data[$key]) ? trim((string) $data[$key]) : '';
        }
        $mapped['nik'] = preg_replace('/\D/', '', $mapped['nik']);
        return $mapped;
    }
}
