<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExport implements FromCollection, WithHeadings
{
    protected $siteId;

    public function __construct($siteId)
    {
        $this->siteId = $siteId;
    }

    public function collection()
    {
        return User::with(['profile', 'site']) // tambahkan relasi site juga
            ->where('site_id', $this->siteId)
            ->get()
            ->map(function ($user) {
                return [
                    'name'             => $user->name,
                    'email'            => $user->email,
                    'employee_nik'     => $user->employee_nik,
                    'phone'            => $user->phone,
                    'site_name'        => optional($user->site)->name,
                    'department_id'    => $user->department_id,
                    'is_employee'      => $user->is_employee,
                    'gender'           => optional($user->profile)->gender,
                    'birth_place'      => optional($user->profile)->birth_place,
                    'birth_date'       => optional($user->profile)->birth_date,
                    'npwp_number'      => optional($user->profile)->npwp_number,
                    'marriage_status'  => optional($user->profile)->marriage_status,
                    'address'          => optional($user->profile)->address,
                    'join_date'        => optional($user->profile)->join_date,
                    'resign_date'      => optional($user->profile)->resign_date,
                    'bank_name'        => optional($user->profile)->bank_name,
                    'account_name'     => optional($user->profile)->account_name,
                    'account_number'   => optional($user->profile)->account_number,
                ];
            });
    }


    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'NIK Karyawan',
            'No. HP',
            'Nama Lokasi (Site)',
            'ID Departemen',
            'Status Karyawan',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Nomor NPWP',
            'Status Pernikahan',
            'Alamat',
            'Tanggal Masuk',
            'Tanggal Resign',
            'Nama Bank',
            'Nama Rekening',
            'Nomor Rekening'
        ];
    }
}

