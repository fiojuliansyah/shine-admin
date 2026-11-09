<?php

use Carbon\Carbon;

if (! function_exists('format_tanggal')) {
    /**
     * Format tanggal standar aplikasi: DD-MM-YYYY.
     *
     * Menerima string tanggal (Y-m-d, d-m-Y, datetime) atau objek tanggal.
     * Nilai kosong mengembalikan $default; nilai yang tidak bisa dibaca dikembalikan apa adanya.
     */
    function format_tanggal(mixed $value, string $default = '-', string $format = 'd-m-Y'): string
    {
        if (blank($value)) {
            return $default;
        }

        try {
            $date = $value instanceof DateTimeInterface ? Carbon::instance($value) : Carbon::parse((string) $value);

            return $date->format($format);
        } catch (Throwable) {
            return (string) $value;
        }
    }
}
