<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Template pesan notifikasi ke pelamar (WhatsApp & Email).
 * Variabel {nama}, {posisi}, dst. diganti saat pesan dikirim.
 */
class MessageTemplate extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Variabel yang bisa dipakai di template => keterangan. */
    public const PLACEHOLDERS = [
        '{nama}' => 'Nama pelamar',
        '{posisi}' => 'Posisi / lowongan yang dilamar',
        '{status}' => 'Status (tingkatan) baru',
        '{email}' => 'Email pelamar',
        '{no_hp}' => 'No. WhatsApp pelamar',
        '{nik_karyawan}' => 'NIK karyawan (jika sudah ada)',
        '{site}' => 'Site / penempatan',
        '{perusahaan}' => 'Nama perusahaan site',
        '{tanggal}' => 'Tanggal hari ini',
        '{no_surat}' => 'Nomor surat (khusus revisi kontrak)',
        '{periode_lama}' => 'Periode kontrak lama (khusus revisi kontrak)',
        '{periode_baru}' => 'Periode kontrak baru (khusus revisi kontrak)',
        '{mulai}' => 'Tanggal mulai kontrak baru (khusus revisi kontrak)',
        '{selesai}' => 'Tanggal akhir kontrak baru (khusus revisi kontrak)',
        '{perubahan}' => 'Daftar perubahan isi surat (khusus revisi surat)',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function valuesFor(Applicant $applicant): array
    {
        $applicant->loadMissing(['user.site.company', 'career', 'status']);

        return array_merge(self::valuesForUser($applicant->user), [
            '{posisi}' => $applicant->career->name ?? '',
            '{status}' => $applicant->status->name ?? '',
        ]);
    }

    /** Nilai variabel untuk user/karyawan (tanpa data lamaran). $extra menimpa/menambah variabel. */
    public static function valuesForUser(?User $user, array $extra = []): array
    {
        $user?->loadMissing('site.company', 'roles');

        return array_merge([
            '{nama}' => $user->name ?? '',
            '{posisi}' => $user?->getRoleNames()->first() ?? '',
            '{status}' => '',
            '{email}' => $user->email ?? '',
            '{no_hp}' => $user->phone ?? '',
            '{nik_karyawan}' => $user->employee_nik ?? '',
            '{site}' => $user->site->name ?? '',
            '{perusahaan}' => $user->site->company->name ?? '',
            '{tanggal}' => format_tanggal(now()),
        ], $extra);
    }

    public function renderFor(array $values): array
    {
        return [
            'subject' => strtr($this->subject ?: 'Pemberitahuan', $values),
            'body' => strtr($this->body, $values),
        ];
    }

    public function renderBody(Applicant $applicant): string
    {
        return strtr($this->body, self::valuesFor($applicant));
    }

    public function renderSubject(Applicant $applicant): string
    {
        $subject = $this->subject ?: 'Update Status Lamaran';

        return strtr($subject, self::valuesFor($applicant));
    }
}
