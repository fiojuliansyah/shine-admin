<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Menerima laporan error dari browser (mis. "DataTables warning: Ajax error") dan
 * menulisnya ke laravel.log. Berguna untuk error yang tidak sempat dicatat server:
 * timeout gateway (504), sesi habis (419), memori/timeout PHP, atau respons bukan JSON.
 */
class ClientLogController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
            'table' => ['nullable', 'string', 'max:100'],
            'page_url' => ['nullable', 'string', 'max:1000'],
            'ajax_url' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'integer'],
            'status_text' => ['nullable', 'string', 'max:200'],
            'response' => ['nullable', 'string', 'max:4000'],
            'params' => ['nullable', 'string', 'max:2000'],
        ]);

        Log::error('Browser: DataTables gagal memuat data', [
            'user_id' => Auth::id(),
            'table' => $data['table'] ?? null,
            'page' => $data['page_url'] ?? null,
            'ajax_url' => $data['ajax_url'] ?? null,
            'params' => $data['params'] ?? null,
            'http_status' => ($data['status'] ?? null) . ' ' . ($data['status_text'] ?? ''),
            'datatables_message' => $data['message'] ?? null,
            // Potongan respons server: isi halaman error / pesan PHP / HTML gateway
            'response' => Str::limit(trim(strip_tags($data['response'] ?? '')), 1500),
            'user_agent' => Str::limit((string) $request->userAgent(), 200),
        ]);

        return response()->noContent();
    }
}
