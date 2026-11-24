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
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function valuesFor(Applicant $applicant): array
    {
        $applicant->loadMissing(['user.site.company', 'career', 'status']);
        $user = $applicant->user;

        return [
            '{nama}' => $user->name ?? '',
            '{posisi}' => $applicant->career->name ?? '',
            '{status}' => $applicant->status->name ?? '',
            '{email}' => $user->email ?? '',
            '{no_hp}' => $user->phone ?? '',
            '{nik_karyawan}' => $user->employee_nik ?? '',
            '{site}' => $user->site->name ?? '',
            '{perusahaan}' => $user->site->company->name ?? '',
            '{tanggal}' => format_tanggal(now()),
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
