<?php

namespace App\Support;

/**
 * Validasi tanda tangan digital (data URI PNG dari signature pad).
 * Hanya menerima "data:image/png;base64,<base64 murni>" yang isinya benar-benar PNG,
 * lalu mengembalikan data URI yang bersih — mencegah HTML/JS disisipkan ke tanda tangan
 * (nilai ini dirender mentah di surat).
 */
class SignatureImage
{
    private const PREFIX = 'data:image/png;base64,';
    private const MAX_BYTES = 2 * 1024 * 1024;

    public static function normalizePng(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(str_replace(' ', '+', $value));
        if (! preg_match('/^data:image\/png;base64,([A-Za-z0-9+\/]+={0,2})$/', $value, $m)) {
            return null;
        }

        $bytes = base64_decode($m[1], true);
        if ($bytes === false || $bytes === '' || strlen($bytes) > self::MAX_BYTES) {
            return null;
        }

        $info = @getimagesizefromstring($bytes);
        if (! $info || ($info['mime'] ?? null) !== 'image/png') {
            return null;
        }

        return self::PREFIX . base64_encode($bytes);
    }
}
