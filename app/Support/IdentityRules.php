<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Aturan validasi bersama untuk data identitas & rekening karyawan / pelamar.
 */
class IdentityRules
{
    /**
     * Buang spasi, titik, dan strip dari nomor identitas sebelum validasi
     * (mis. "3201 2345 6789 0123" atau rekening "123-456-789").
     */
    public static function normalize(Request $request, array $fields = ['nik', 'no_kk', 'account_number']): void
    {
        $clean = [];

        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $clean[$field] = preg_replace('/[\s.\-]+/', '', (string) $request->input($field));
            }
        }

        $request->merge($clean);
    }

    public static function nik(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'digits:16'];
    }

    public static function kk(): array
    {
        return ['nullable', 'digits:16'];
    }

    /**
     * Data rekening wajib (dibutuhkan penggajian). Memakai "sometimes" karena form profil
     * lain (data diri, kontak darurat) memakai endpoint yang sama tanpa mengirim field ini.
     */
    public static function bank(): array
    {
        return [
            'bank_name' => ['sometimes', 'required', 'string', 'max:100'],
            'account_name' => ['sometimes', 'required', 'string', 'max:150'],
            'account_number' => ['sometimes', 'required', 'digits_between:5,20'],
        ];
    }

    public static function messages(): array
    {
        return [
            'nik.required' => 'NIK KTP wajib diisi.',
            'nik.digits' => 'NIK KTP wajib 16 digit angka (tidak boleh lebih atau kurang).',
            'no_kk.digits' => 'No Kartu Keluarga wajib 16 digit angka.',
            'bank_name.required' => 'Nama bank wajib diisi (dibutuhkan untuk penggajian).',
            'account_name.required' => 'Nama pemilik rekening wajib diisi (dibutuhkan untuk penggajian).',
            'account_number.required' => 'No rekening wajib diisi (dibutuhkan untuk penggajian).',
            'account_number.digits_between' => 'No rekening hanya boleh berisi angka (5–20 digit).',
        ];
    }
}
