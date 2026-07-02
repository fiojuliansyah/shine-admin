<?php

namespace App\Imports;

use App\Models\User;
use App\Models\SalarySetting;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class SalarySettingsImport implements ToCollection, WithStartRow
{
    protected $siteId;

    protected int $imported = 0;
    protected int $skipped = 0;
    protected array $skippedNiks = [];

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

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $employeeNik = trim((string) ($row[1] ?? ''));

            if ($employeeNik === '') {
                continue;
            }

            $userQuery = User::where('employee_nik', $employeeNik);
            if ($this->siteId) {
                $userQuery->where('site_id', $this->siteId);
            }
            $user = $userQuery->first();

            if (!$user) {
                $this->skipped++;
                $this->skippedNiks[] = $employeeNik;
                continue;
            }

            $data = [];
            foreach (self::$componentFields as $i => $field) {
                $value = $row[4 + $i] ?? 0;
                $data[$field] = $this->toNumber($value);
            }

            SalarySetting::updateOrCreate(['user_id' => $user->id], $data);
            $this->imported++;
        }
    }

    protected function toNumber($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = preg_replace('/[^0-9,.\-]/', '', (string) $value);
        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function getImported(): int
    {
        return $this->imported;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    public function getSkippedNiks(): array
    {
        return $this->skippedNiks;
    }
}
