<?php

namespace App\Traits;

trait PdfPageImageTrait
{
    protected function savePageImages(array $pages): array
    {
        foreach ($pages as &$page) {
            // pageImagePath hanya boleh berasal dari server (file sementara di bawah),
            // jangan percaya nilai dari JSON template.
            unset($page['pageImagePath']);

            if (!empty($page['pageImage']) && is_string($page['pageImage']) && str_starts_with($page['pageImage'], 'data:image/')) {
                $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $page['pageImage']);
                $tmpBase = tempnam(sys_get_temp_dir(), 'pdf_page_');
                @unlink($tmpBase); // tempnam membuat file kosong; yang dipakai versi .png
                $tmpFile = $tmpBase . '.png';
                file_put_contents($tmpFile, base64_decode($base64));
                $page['pageImagePath'] = $tmpFile;
            }
        }
        return $pages;
    }

    protected function cleanPageImages(array $pages): void
    {
        $tmpDir = realpath(sys_get_temp_dir());

        foreach ($pages as $page) {
            $path = $page['pageImagePath'] ?? null;
            if (!is_string($path) || !file_exists($path)) {
                continue;
            }

            // Hanya hapus file sementara buatan savePageImages()
            $real = realpath($path);
            if ($real && dirname($real) === $tmpDir && str_starts_with(basename($real), 'pdf_page_')) {
                unlink($real);
            }
        }
    }
}
