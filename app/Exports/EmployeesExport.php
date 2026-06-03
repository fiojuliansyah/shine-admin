<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $siteId;

    public function __construct($siteId = null)
    {
        $this->siteId = $siteId;
    }

    public function collection()
    {
        $query = User::with(['profile', 'site', 'roles'])
            ->where('is_employee', 1)
            ->where('is_admin', 0);

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        return $query->get()->map(function ($user, $index) {
            $profile = $user->profile;
            $alamat  = implode(', ', array_filter([
                optional($profile)->address,
                optional($profile)->rt_rw,
                optional($profile)->kelurahan ? 'Kel. ' . optional($profile)->kelurahan : null,
                optional($profile)->kecamatan ? 'Kec. ' . optional($profile)->kecamatan : null,
            ]));

            return [
                'no'               => $index + 1,
                'name'             => $user->name,
                'nik'              => $user->nik,
                'employee_nik'     => $user->employee_nik,
                'email'            => $user->email,
                'phone'            => $user->phone,
                'jabatan'          => $user->getRoleNames()->implode(', '),
                'site'             => optional($user->site)->name,
                'gender'           => optional($profile)->gender,
                'birth_place'      => optional($profile)->birth_place,
                'birth_date'       => optional($profile)->birth_date,
                'address'          => $alamat,
                'marriage_status'  => optional($profile)->marriage_status,
                'last_education'   => optional($profile)->last_education,
                'npwp_number'      => optional($profile)->npwp_number,
                'bank_name'        => optional($profile)->bank_name,
                'account_name'     => optional($profile)->account_name,
                'account_number'   => optional($profile)->account_number,
                'join_date'        => optional($profile)->join_date,
                'resign_date'      => optional($profile)->resign_date,
                'status'           => optional($profile)->resign_date ? 'Resign' : 'Aktif',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'NIK KTP',
            'NIK Karyawan',
            'Email',
            'No. HP',
            'Jabatan',
            'Site',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'Status Pernikahan',
            'Pendidikan Terakhir',
            'No. NPWP',
            'Bank',
            'Nama Rekening',
            'No. Rekening',
            'Tanggal Masuk',
            'Tanggal Resign',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E3A5F']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
