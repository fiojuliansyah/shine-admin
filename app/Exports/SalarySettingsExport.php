<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalarySettingsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $siteId;

    public static array $componentFields = [
        'gaji_pokok',
        'tunj_jabatan',
        'tunj_kehadiran',
        'tunj_komunikasi',
        'tunj_makan',
        'tunj_transport',
        'tunj_lembur_tetap',
        'tunj_other_non_fix',
    ];

    public function __construct($siteId = null)
    {
        $this->siteId = $siteId;
    }

    public function collection()
    {
        $query = User::with(['site', 'salarySetting'])
            ->where('is_employee', 1)
            ->orderBy('name');

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        return $query->get()->values()->map(function ($user, $index) {
            $setting = $user->salarySetting;

            $row = [
                'no'           => $index + 1,
                'employee_nik' => $user->employee_nik,
                'name'         => $user->name,
                'site'         => optional($user->site)->name,
            ];

            foreach (self::$componentFields as $field) {
                $row[$field] = (float) ($setting->$field ?? 0);
            }

            return $row;
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK Karyawan',
            'Nama',
            'Site',
            'Gaji Pokok',
            'Tunjangan Jabatan',
            'Tunjangan Kehadiran',
            'Tunjangan Komunikasi',
            'Tunjangan Makan',
            'Tunjangan Transport',
            'Tunjangan Lembur Tetap',
            'Tunjangan Other Non Fix',
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
