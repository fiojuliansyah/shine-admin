<?php

namespace App\Support;

use App\Models\Document;
use App\Models\User;

/**
 * Satu sumber syarat kelengkapan pelamar, dipakai di dashboard pelamar,
 * halaman profil pelamar, dan saat melamar (apply).
 */
class ApplicantRequirements
{
    /** Kolom profil wajib => label tampilan. */
    public const PROFILE_FIELDS = [
        'avatar_url' => 'Pas Foto',
        'gender' => 'Jenis Kelamin',
        'birth_place' => 'Tempat Lahir',
        'birth_date' => 'Tanggal Lahir',
        'mother_name' => 'Nama Ibu Kandung',
        'no_kk' => 'No Kartu Keluarga',
        'last_education' => 'Pendidikan Terakhir',
        'marriage_status' => 'Status Pernikahan',
        'living_with' => 'Status Tempat Tinggal',
        'height' => 'Tinggi Badan',
        'weight' => 'Berat Badan',
        'eye_condition' => 'Kondisi Mata',
        'hearing' => 'Pendengaran',
        'address' => 'Alamat KTP',
        'current_address' => 'Alamat Domisili',
        'family_name' => 'Nama Kontak Darurat',
        'family_relation' => 'Hubungan Kontak Darurat',
        'family_phone' => 'No. Telp Kontak Darurat',
        'bank_name' => 'Nama Bank',
        'account_name' => 'Nama Pemilik Rekening',
        'account_number' => 'No Rekening',
    ];

    /** Dokumen yang selalu wajib saat melamar. */
    public const DOCUMENTS = ['KTP', 'IJAZAH', 'KARTU KELUARGA', 'SKCK'];

    /** Sertifikat GADA wajib bila pelamar menyatakan memilikinya. */
    private const GADA_DOCUMENTS = [
        'gada_pratama' => 'GADA PRATAMA',
        'gada_madya' => 'GADA MADYA',
        'gada_utama' => 'GADA UTAMA',
    ];

    /** Profil & dokumen wajib sudah lengkap (dipakai wizard "Lengkapi Profil"). */
    public static function isComplete(User $user): bool
    {
        $user->loadMissing('profile');

        return empty(self::missingFields($user)) && empty(self::missingDocuments($user));
    }

    /** @return string[] */
    public static function requiredDocuments(User $user): array
    {
        $documents = self::DOCUMENTS;

        foreach (self::GADA_DOCUMENTS as $field => $document) {
            if ($user->profile?->{$field} === 'yes') {
                $documents[] = $document;
            }
        }

        return $documents;
    }

    /**
     * Label data yang belum lengkap, termasuk NIK KTP di akun (wajib 16 digit).
     *
     * @return string[]
     */
    public static function missingFields(User $user): array
    {
        $missing = [];

        if (! preg_match('/^\d{16}$/', (string) $user->nik)) {
            $missing[] = 'NIK KTP (16 digit)';
        }

        foreach (self::PROFILE_FIELDS as $field => $label) {
            if (blank($user->profile?->{$field})) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    /**
     * @param  string[]|null  $uploaded  nama dokumen yang sudah diunggah (null = ambil dari DB)
     * @return string[]
     */
    public static function missingDocuments(User $user, ?array $uploaded = null): array
    {
        $uploaded ??= Document::where('user_id', $user->id)->pluck('name')->all();

        return array_values(array_diff(self::requiredDocuments($user), $uploaded));
    }
}
