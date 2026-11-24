<?php

namespace App\Support;

/**
 * Pengamanan nilai variabel surat ([nama_karyawan], [alamat], variabel kustom, dst.)
 * sebelum disisipkan ke template. Nilai berasal dari data yang bisa diisi pelamar,
 * jadi wajib di-escape agar tidak bisa menyisipkan HTML/JS (XSS) atau merusak JSON.
 */
class LetterPlaceholders
{
    /** Untuk template HTML (teks): escape HTML, baris baru -> <br>. */
    public static function forHtml(array $replace): array
    {
        return array_map(fn ($value) => nl2br(e(self::stringify($value)), false), $replace);
    }

    /** Untuk template kanvas (JSON Fabric): escape sebagai isi string JSON. */
    public static function forJson(array $replace): array
    {
        return array_map(
            fn ($value) => substr(json_encode(self::stringify($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 1, -1),
            $replace
        );
    }

    private static function stringify($value): string
    {
        return is_scalar($value) || $value === null ? (string) $value : '';
    }
}
