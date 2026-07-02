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

        $content = $this->templatePlainText($letter->description ?? '');

        // Variabel tetap: hanya yang benar-benar dipakai di template.
        foreach ($this->inputFixedVariableMap() as $token => [$column, $label]) {
            if ($this->tokenUsed($content, $token)) {
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

    /**
     * Ekstrak teks polos dari body template, mendukung tiga bentuk penyimpanan:
     * - Canvas/Fabric JSON ({"pages":[{"canvasJSON":{"objects":[{"text":"..."}]}}]})
     * - HTML (editor teks / TinyMCE)
     * - Teks biasa
     */
    protected function templatePlainText($description): string
    {
        $raw = (string) $description;
        if ($raw === '') {
            return '';
        }

        $text = $raw;

        // Coba parse sebagai Fabric JSON dan kumpulkan seluruh nilai "text".
        $parsed = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
            $collected = [];
            array_walk_recursive($parsed, function ($value, $key) use (&$collected) {
                if ($key === 'text' && is_string($value)) {
                    $collected[] = $value;
                }
            });
            if (!empty($collected)) {
                $text = implode(' ', $collected);
            }
        }

        // Buang tag HTML dan decode entitas agar token tidak terpecah oleh markup.
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);

        return $text;
    }

    /**
     * Cek token dipakai, toleran terhadap spasi di dalam kurung (mis. "[ mulai ]").
     */
    protected function tokenUsed(string $content, string $token): bool
    {
        if (str_contains($content, $token)) {
            return true;
        }

        $inner = trim($token, '[]');
        $pattern = '/\[\s*' . preg_quote($inner, '/') . '\s*\]/i';

        return (bool) preg_match($pattern, $content);
    }
}
