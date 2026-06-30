<?php

namespace App\Traits;

use App\Models\User;

trait SalaryVariableTrait
{
    /**
     * Daftar field pengaturan gaji => placeholder variabel template.
     */
    protected function salaryVariableMap(): array
    {
        return [
            'gaji_pokok'         => '[gaji_pokok]',
            'tunj_jabatan'       => '[tunj_jabatan]',
            'tunj_kehadiran'     => '[tunj_kehadiran]',
            'tunj_komunikasi'    => '[tunj_komunikasi]',
            'tunj_makan'         => '[tunj_makan]',
            'tunj_transport'     => '[tunj_transport]',
            'tunj_lembur_tetap'  => '[tunj_lembur_tetap]',
            'tunj_other_non_fix' => '[tunj_other_non_fix]',
        ];
    }

    /**
     * Bangun pasangan search/replace untuk variabel pengaturan gaji,
     * nilai diformat sebagai Rupiah (mis. Rp 1.500.000).
     *
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    protected function buildSalaryVariables(?User $user): array
    {
        $search = [];
        $replace = [];
        $setting = $user?->salarySetting;

        foreach ($this->salaryVariableMap() as $field => $placeholder) {
            $value = $setting?->{$field} ?? 0;
            $search[] = $placeholder;
            $replace[] = 'Rp ' . number_format((float) $value, 0, ',', '.');
        }

        return [$search, $replace];
    }
}
