<?php

namespace App\Traits;

use App\Models\Letter;

trait GenerateTemplateColumns
{
    /**
     * Variabel tetap yang merupakan input saat penerbitan surat
     * (tersimpan di record Generate), beserta kolom & labelnya.
     *
     * token => [generate_column, label]
     */
    protected function inputFixedVariableMap(): array
    {
        return [
            '[pihak_2]'       => ['second_party', 'Pihak Kedua / Nama'],
            '[mulai]'         => ['start_date', 'Tanggal Mulai (YYYY-MM-DD)'],
            '[selesai]'       => ['end_date', 'Tanggal Selesai (YYYY-MM-DD)'],
            '[hari]'          => ['day', 'Hari'],
            '[nama_kontak]'   => ['emergency_name', 'Nama Kontak Darurat'],
            '[no_kontak]'     => ['emergency_number', 'No. Kontak Darurat'],
            '[alamat_kontak]' => ['emergency_address', 'Alamat Kontak Darurat'],
            '[hubungan]'      => ['relationship', 'Hubungan Kontak Darurat'],
        ];
    }

    /**
     * Bangun daftar kolom terurut untuk template export/import berdasarkan
     * variabel tetap yang dipakai template + variabel kustomnya.
     *
     * Setiap kolom: [
     *   'type'  => 'nik'|'fixed'|'custom',
     *   'label' => string,
     *   'key'   => generate column (untuk fixed) atau custom_variable_id (untuk custom),
     * ]
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildTemplateColumns(Letter $letter): array
    {
        $columns = [];

        // Kolom kunci wajib: NIK Karyawan untuk mencocokkan employee.
        $columns[] = [
            'type'  => 'nik',
            'label' => 'NIK Karyawan',
            'key'   => 'employee_nik',
        ];

        $description = (string) ($letter->description ?? '');

        // Variabel tetap: hanya yang benar-benar dipakai di template.
        foreach ($this->inputFixedVariableMap() as $token => [$column, $label]) {
            if (str_contains($description, $token)) {
                $columns[] = [
                    'type'  => 'fixed',
                    'label' => $label,
                    'key'   => $column,
                ];
            }
        }

        // Variabel kustom milik template.
        foreach ($letter->customVariables as $cv) {
            $columns[] = [
                'type'  => 'custom',
                'label' => $cv->name . ' [' . $cv->variable . ']',
                'key'   => $cv->id,
            ];
        }

        return $columns;
    }
}
