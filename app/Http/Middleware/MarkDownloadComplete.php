<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Form yang memicu download file tidak berpindah halaman, sehingga popup
 * "Sedang memproses..." tidak pernah tertutup. JS global mengirim
 * `download_token`; bila responsnya berupa file, token dikembalikan lewat
 * cookie agar halaman tahu download sudah dimulai dan menutup popup.
 */
class MarkDownloadComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $token = $request->input('download_token');
        if (! is_string($token) || ! preg_match('/^[A-Za-z0-9]{1,40}$/', $token)) {
            return $response;
        }

        $isDownload = $response instanceof BinaryFileResponse
            || $response instanceof StreamedResponse
            || str_contains(strtolower((string) $response->headers->get('Content-Disposition')), 'attachment');

        if ($isDownload) {
            $response->headers->setCookie(
                Cookie::create('download_token', $token, 0, '/', null, null, false, false, Cookie::SAMESITE_LAX)
            );
        }

        return $response;
    }
}
