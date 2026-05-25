<?php

namespace App\Traits;

trait PdfPageImageTrait
{
    protected function savePageImages(array $pages): array
    {
        foreach ($pages as &$page) {
            if (!empty($page['pageImage']) && str_starts_with($page['pageImage'], 'data:image/')) {
                $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $page['pageImage']);
                $tmpFile = tempnam(sys_get_temp_dir(), 'pdf_page_') . '.png';
                file_put_contents($tmpFile, base64_decode($base64));
                $page['pageImagePath'] = $tmpFile;
            }
        }
        return $pages;
    }

    protected function cleanPageImages(array $pages): void
    {
        foreach ($pages as $page) {
            if (!empty($page['pageImagePath']) && file_exists($page['pageImagePath'])) {
                unlink($page['pageImagePath']);
            }
        }
    }
}
