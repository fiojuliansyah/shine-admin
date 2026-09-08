<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeDataExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(private readonly ?int $siteId = null, private readonly bool $template = false) {}

    public function collection()
    {
        if ($this->template) {
            return collect();
        }

        return User::with(['profile', 'site.company', 'roles', 'leader'])
            ->where('is_employee', 1)
            ->when($this->siteId, fn ($query) => $query->where('site_id', $this->siteId))
            ->get()
            ->map(function (User $user) {
                $profile = $user->profile;
                $date = fn ($value) => $value ? date('d-m-Y', strtotime($value)) : null;

                return [
                    $profile->employment_status === 'RESIGN' || $profile->resign_date ? 'RESIGN' : 'ACTIVE',
                    $user->site?->company?->short_name,
                    $user->employee_nik,
                    $user->name,
                    $user->email,
                    $user->phone,
                    $profile->mother_name,
                    $user->roles->first()?->code ?? $user->roles->first()?->name,
                    $user->site?->name,
                    $user->site?->area,
                    $profile->birth_place,
                    $date($profile->birth_date),
                    $profile->gender,
                    $user->nik,
                    $profile->npwp_number,
                    $profile->no_kk,
                    $profile->address,
                    $profile->religion,
                    $profile->marriage_status,
                    $user->leader?->employee_nik,
                    $date($profile->join_date),
                    $date($profile->end_date),
                    $date($profile->mutation_date),
                    $date($profile->resign_date),
                    $profile->account_number,
                    $profile->bank_name,
                    $profile->account_name,
                    $profile->bpjs_tk_number,
                    $profile->bpjs_kes_number,
                    $profile->keterangan,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Status', 'PT', 'NIK Karyawan', 'Nama', 'Email', 'Handphone',
            'Nama Ibu Kandung', 'Jabatan', 'Project', 'Area/Wilayah',
            'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin', 'No NIK KTP',
            'No NPWP', 'No KK', 'Alamat KTP', 'Agama', 'Status Pernikahan',
            'Manager', 'Join Date', 'End Date', 'TGL Mutasi', 'TGL Resign',
            'No Rekening', 'Nama Bank', 'Nama Rekenig', 'No BPJS TK',
            'No BPJS KES', 'Keterangan',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E3A5F']],
        ]];
    }
}
