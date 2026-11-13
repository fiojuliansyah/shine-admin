<?php

namespace App\Http\Controllers;

use App\Services\FileStorage;
use Illuminate\Support\Str;

/**
 * Melayani file publik (avatar, foto wajah, logo, gambar surat) lewat aplikasi.
 *
 * Dipakai bila provider S3 tidak mengizinkan akses publik ke bucket (mis. Cloudeka):
 * set AWS_URL=${APP_URL}/media, maka URL file menjadi /media/{path} dan rute ini
 * me-redirect ke link bertanda tangan S3. Folder private (dokumen, e-sign) selalu 404 di sini.
 */
class MediaController extends Controller
{
    public function __invoke(string $path)
    {
        $path = ltrim($path, '/');

        abort_if($path === '' || str_contains($path, '..'), 404);
        abort_unless(in_array(Str::before($path, '/'), FileStorage::PUBLIC_FOLDERS, true), 404);

        $disk = FileStorage::disk();
        abort_unless($disk->providesTemporaryUrls(), 404);

        return redirect()->away($disk->temporaryUrl($path, now()->addHours(12)))
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
