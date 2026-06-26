<?php

namespace App\Support;

class EmployeeColumnConfig
{
    public static function getColumns(string $companyKey): array
    {
        $key = strtoupper(trim($companyKey));

        return match (true) {
            str_contains($key, 'CMAJ') => self::cmaj(),
            str_contains($key, 'IJI')  => self::iji(),
            str_contains($key, 'GSI')  => self::gsi(),
            default                    => self::default(),
        };
    }

    public static function cmaj(): array
    {
        return [
            ['key' => 'no_surat',         'label' => 'NO SURAT'],
            ['key' => 'employee_nik',      'label' => 'NIK'],
            ['key' => 'company_name',      'label' => 'NAMA PERUSAHAAN'],
            ['key' => 'tgl_aktif',         'label' => 'TGL AKTIF'],
            ['key' => 'bln_aktif',         'label' => 'BLN AKTIF'],
            ['key' => 'thn_aktif',         'label' => 'THN AKTIF'],
            ['key' => 'name',              'label' => 'NAMA'],
            ['key' => 'email',             'label' => 'EMAIL'],
            ['key' => 'phone',             'label' => 'NO HP AKTIF'],
            ['key' => 'mother_name',       'label' => 'NAMA IBU KANDUNG'],
            ['key' => 'jabatan1',          'label' => 'JABATAN 1'],
            ['key' => 'area',              'label' => 'AREA'],
            ['key' => 'birth_place',       'label' => 'TEMPAT LAHIR'],
            ['key' => 'birth_date',        'label' => 'TANGGAL LAHIR'],
            ['key' => 'gender',            'label' => 'JENIS KELAMIN'],
            ['key' => 'nik',               'label' => 'NO KTP'],
            ['key' => 'no_kk',             'label' => 'NO KK'],
            ['key' => 'npwp_number',       'label' => 'NPWP'],
            ['key' => 'address',           'label' => 'ALAMAT KTP'],
            ['key' => 'religion',          'label' => 'AGAMA'],
            ['key' => 'marriage_status',   'label' => 'STATUS KAWIN'],
            ['key' => 'klien',             'label' => 'KLIEN'],
            ['key' => 'up',                'label' => 'UP'],
            ['key' => 'jabatan2',          'label' => 'JABATAN 2'],
            ['key' => 'no_srt',            'label' => 'NO SRT'],
            ['key' => 'join_or',           'label' => 'JOIN OR'],
            ['key' => 'bln_or',            'label' => 'BLN OR'],
            ['key' => 'thn_or',            'label' => 'THN OR'],
            ['key' => 'tgl_balik',         'label' => 'Tanggal balik kekantor'],
            ['key' => 'keterangan',        'label' => 'KETERANGAN'],
            ['key' => 'ket_pengganti',     'label' => 'KETERANG PENGGANTI'],
            ['key' => 'request',           'label' => 'REQUEST'],
            ['key' => 'bank_name',         'label' => 'NAMA BANK'],
            ['key' => 'account_number',    'label' => 'NOMOR REKENING'],
        ];
    }

    public static function iji(): array
    {
        return [
            ['key' => 'no_surat',         'label' => 'NO SURAT'],
            ['key' => 'employee_nik',      'label' => 'NIK'],
            ['key' => 'company_name',      'label' => 'NAMA PERUSAHAAN'],
            ['key' => 'tgl_pm',            'label' => 'TGL PM'],
            ['key' => 'bln_pem',           'label' => 'BLN PEM'],
            ['key' => 'thn_pem',           'label' => 'THN PEM'],
            ['key' => 'name',              'label' => 'NAMA'],
            ['key' => 'email',             'label' => 'EMAIL'],
            ['key' => 'mother_name',       'label' => 'IBU KANDUNG'],
            ['key' => 'birth_place',       'label' => 'TEMPAT LAHIR'],
            ['key' => 'birth_date',        'label' => 'Tgl Lahir'],
            ['key' => 'gender',            'label' => 'JK'],
            ['key' => 'nik',               'label' => 'NO KTP'],
            ['key' => 'no_kk',             'label' => 'NO KK'],
            ['key' => 'npwp_number',       'label' => 'NO NPWP'],
            ['key' => 'address',           'label' => 'ALAMAT KTP'],
            ['key' => 'phone',             'label' => 'NO HP'],
            ['key' => 'religion',          'label' => 'AGAMA'],
            ['key' => 'marriage_status',   'label' => 'STATUS KAWIN'],
            ['key' => 'jabatan1',          'label' => 'JABATAN 1'],
            ['key' => 'area',              'label' => 'AREA'],
            ['key' => 'klien',             'label' => 'KLIEN'],
            ['key' => 'up',                'label' => 'UP'],
            ['key' => 'jabatan2',          'label' => 'JABATAN 2'],
            ['key' => 'no_srt',            'label' => 'NO SRT'],
            ['key' => 'join_or',           'label' => 'JOIN OR'],
            ['key' => 'bln_or',            'label' => 'BLN OR'],
            ['key' => 'thn_or',            'label' => 'THN OR'],
            ['key' => 'keterangan',        'label' => 'KETERANGAN'],
            ['key' => 'keterangan2',       'label' => 'KETERANGAN 2'],
            ['key' => 'request',           'label' => 'REQUEST'],
            ['key' => 'pengganti',         'label' => 'PENGGANTI'],
            ['key' => 'resign_date',       'label' => 'TANGGAL RESIGN'],
            ['key' => 'bank_name',         'label' => 'NAMA BANK'],
            ['key' => 'account_number',    'label' => 'NO REKENING'],
        ];
    }

    public static function gsi(): array
    {
        return [
            ['key' => 'no_surat',         'label' => 'NO SURAT'],
            ['key' => 'employee_nik',      'label' => 'NIK'],
            ['key' => 'company_name',      'label' => 'NAMA PERUSAHAAN'],
            ['key' => 'tgl_pm',            'label' => 'TGL PM'],
            ['key' => 'bln_pem',           'label' => 'BLN PEM'],
            ['key' => 'thn_pem',           'label' => 'THN PEM'],
            ['key' => 'name',              'label' => 'NAMA'],
            ['key' => 'email',             'label' => 'EMAIL'],
            ['key' => 'birth_place',       'label' => 'TEMPAT LAHIR'],
            ['key' => 'birth_date',        'label' => 'TGL LAHIR'],
            ['key' => 'gender',            'label' => 'JK'],
            ['key' => 'nik',               'label' => 'NO KTP'],
            ['key' => 'no_kk',             'label' => 'NO KK'],
            ['key' => 'npwp_number',       'label' => 'NPWP'],
            ['key' => 'address',           'label' => 'ALAMAT KTP'],
            ['key' => 'religion',          'label' => 'AGAMA'],
            ['key' => 'marriage_status',   'label' => 'STATUS'],
            ['key' => 'phone',             'label' => 'NO HP'],
            ['key' => 'mother_name',       'label' => 'IBU KANDUNG'],
            ['key' => 'jabatan1',          'label' => 'JABATAN 1'],
            ['key' => 'area',              'label' => 'AREA'],
            ['key' => 'klien',             'label' => 'KLIEN'],
            ['key' => 'up',                'label' => 'UP'],
            ['key' => 'jabatan2',          'label' => 'JABATAN 2'],
            ['key' => 'no_srt',            'label' => 'NO SRT'],
            ['key' => 'join_or',           'label' => 'JOIN OR'],
            ['key' => 'bln_or',            'label' => 'BLN OR'],
            ['key' => 'thn_or',            'label' => 'THN OR'],
            ['key' => 'keterangan',        'label' => 'KETERANGAN'],
            ['key' => 'request',           'label' => 'REQUEST'],
            ['key' => 'ket_pengganti',     'label' => 'KETERANGAN NAMA PENGGANTI'],
            ['key' => 'resign_date',       'label' => 'TANGGAL RESIGN'],
            ['key' => 'bln_resign',        'label' => 'BULAN'],
            ['key' => 'thn_resign',        'label' => 'TAHUN'],
            ['key' => 'bank_name',         'label' => 'NAMA BANK'],
            ['key' => 'account_number',    'label' => 'REKENING'],
            ['key' => 'account_name',      'label' => 'NAMA DI REKENING'],
        ];
    }

    public static function default(): array
    {
        return [
            ['key' => 'employee_nik',    'label' => 'NIK'],
            ['key' => 'name',            'label' => 'NAMA'],
            ['key' => 'email',           'label' => 'EMAIL'],
            ['key' => 'phone',           'label' => 'NO HP'],
            ['key' => 'jabatan1',        'label' => 'JABATAN'],
            ['key' => 'area',            'label' => 'AREA'],
            ['key' => 'birth_place',     'label' => 'TEMPAT LAHIR'],
            ['key' => 'birth_date',      'label' => 'TGL LAHIR'],
            ['key' => 'religion',        'label' => 'AGAMA'],
            ['key' => 'address',         'label' => 'ALAMAT'],
            ['key' => 'join_date',       'label' => 'TGL MASUK'],
            ['key' => 'resign_date',     'label' => 'TGL RESIGN'],
            ['key' => 'bank_name',       'label' => 'NAMA BANK'],
            ['key' => 'account_number',  'label' => 'NO REKENING'],
        ];
    }
}
