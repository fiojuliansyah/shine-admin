<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OCR KTP lewat Amanai (https://amanai.sh/docs/), endpoint /chat/completions
 * yang kompatibel OpenAI. Base URL, API key, dan model diatur di .env:
 * AMANAI_BASE_URL, AMANAI_API_KEY, AMANAI_OCR_MODEL.
 */
class KtpOcrController extends Controller
{
    private const FIELDS = ['nik', 'name', 'gender', 'birth_place', 'birth_date', 'address', 'rt_rw', 'kelurahan', 'kecamatan'];

    public function scan(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:png,jpg,jpeg,webp|max:8192',
        ]);

        $apiKey = config('services.amanai.key');
        $baseUrl = rtrim((string) config('services.amanai.base_url'), '/');
        if (empty($apiKey) || $baseUrl === '') {
            return response()->json([
                'message' => 'AMANAI_API_KEY / AMANAI_BASE_URL belum dikonfigurasi di .env server.',
            ], 500);
        }

        $file = $request->file('image');
        $dataUrl = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));

        $instruction = 'Anda adalah mesin ekstraksi data KTP Indonesia. '
            . 'Baca gambar KTP dan kembalikan HANYA JSON valid tanpa teks lain dan tanpa markdown, dengan struktur: '
            . '{"nik":"","name":"","gender":"","birth_place":"","birth_date":"","address":"","rt_rw":"","kelurahan":"","kecamatan":""}. '
            . 'Aturan: nik hanya 16 digit angka; gender bernilai "Laki-Laki" atau "Perempuan"; '
            . 'birth_date format YYYY-MM-DD; rt_rw format contoh "001/002"; '
            . 'kosongkan string jika tidak terbaca. Jangan menambahkan field lain.';

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(90)
                // Coba ulang hanya untuk gangguan sementara (koneksi, rate limit, model sibuk).
                ->retry(2, 1000, fn ($e) => $e instanceof ConnectionException
                    || ($e instanceof RequestException && in_array($e->response->status(), [429, 503], true)), throw: false)
                ->post($baseUrl . '/chat/completions', [
                    'model' => config('services.amanai.ocr_model'),
                    'temperature' => 0,
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
            return response()->json(['message' => 'Gagal menghubungi layanan OCR: ' . $e->getMessage()], 502);
        }

        if ($response->failed() || $response->json('error')) {
            Log::warning('OCR KTP (Amanai) gagal', [
                'status' => $response->status(),
                'request_id' => $response->header('X-Amanai-Request-ID'),
                'error' => $response->json('error'),
            ]);

            return response()->json([
                'message' => 'OCR gagal: ' . $response->json('error.message', 'HTTP ' . $response->status()),
            ], $response->failed() ? $response->status() : 502);
        }

        $data = $this->decodeJson((string) $response->json('choices.0.message.content'));
        if (! is_array($data)) {
            return response()->json(['message' => 'Hasil OCR tidak dapat dibaca. Coba foto KTP yang lebih jelas.'], 422);
        }

        $mapped = [];
        foreach (self::FIELDS as $key) {
            $mapped[$key] = isset($data[$key]) && is_scalar($data[$key]) ? trim((string) $data[$key]) : '';
        }
        $mapped['nik'] = preg_replace('/\D/', '', $mapped['nik']);

        return response()->json(['mapped' => $mapped]);
    }

    /**
     * Model kadang membungkus JSON dengan ```json ... ``` atau teks tambahan;
     * ambil objek JSON pertama yang valid.
     */
    private function decodeJson(string $content): ?array
    {
        $content = trim(preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content)));

        $data = json_decode($content, true);
        if (is_array($data)) {
            return $data;
        }

        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $data = json_decode(substr($content, $start, $end - $start + 1), true);

        return is_array($data) ? $data : null;
    }
}
