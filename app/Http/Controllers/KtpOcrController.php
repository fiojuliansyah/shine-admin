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
}
