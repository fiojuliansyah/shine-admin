<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\FileStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Menampilkan/mengunduh file dokumen (KTP, KK, Ijazah, dst.) lewat aplikasi, bukan link storage.
 * - admin (route manage): semua dokumen
 * - pelamar/karyawan (route applicant): hanya dokumen miliknya
 * Dipakai modal pratinjau dokumen (partials/document-preview).
 */
class DocumentFileController extends Controller
{
    public function admin(Request $request, Document $document)
    {
        return $this->stream($request, $document);
    }

    public function own(Request $request, Document $document)
    {
        abort_unless((int) $document->user_id === (int) Auth::id(), 404);

        return $this->stream($request, $document);
    }

    private function stream(Request $request, Document $document)
    {
        $path = $document->file_public_id;
        $onDisk = filled($path) && (blank($document->file_url) || FileStorage::pathFromUrl($document->file_url) !== null);
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';
        $filename = Str::slug($document->name ?: 'dokumen') . '.' . (pathinfo((string) ($path ?: $document->file_url), PATHINFO_EXTENSION) ?: 'jpg');

        if ($onDisk && FileStorage::disk()->exists($path)) {
            return FileStorage::disk()->response($path, $filename, [
                'Cache-Control' => 'private, max-age=600',
                'X-Content-Type-Options' => 'nosniff',
            ], $disposition);
        }

        // Data lama (mis. Cloudinary) yang tidak ada di disk aplikasi
        abort_if(blank($document->file_url), 404);

        return redirect()->away($document->view_url);
    }
}
