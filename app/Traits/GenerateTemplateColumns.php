<?php

namespace App\Traits;

use App\Models\Letter;

trait GenerateTemplateColumns
{
    /**
     * Seluruh variabel tetap template.
     *
     * token => [
     *   'mode'   => 'input'|'auto',   // input = diisi saat import; auto = dari data karyawan
     *   'column' => generate column atau null (khusus input),
     *   'label'  => label kolom,
     * ]
     */
    protected function fixedVariableMap(): array
    {
        return [
            // Variabel input per-surat (disimpan ke record Generate saat import).
            '[pihak_2]'       => ['mode' => 'input', 'column' => 'second_party',     'label' => 'Pihak Kedua / Nama'],
            '[mulai]'         => ['mode' => 'input', 'column' => 'start_date',       'label' => 'Tanggal Mulai (YYYY-MM-DD)'],
            '[selesai]'       => ['mode' => 'input', 'column' => 'end_date',         'label' => 'Tanggal Selesai (YYYY-MM-DD)'],
            '[hari]'          => ['mode' => 'input', 'column' => 'day',              'label' => 'Hari'],
            '[nama_kontak]'   => ['mode' => 'input', 'column' => 'emergency_name',   'label' => 'Nama Kontak Darurat'],
            '[no_kontak]'     => ['mode' => 'input', 'column' => 'emergency_number', 'label' => 'No. Kontak Darurat'],
            '[alamat_kontak]' => ['mode' => 'input', 'column' => 'emergency_address','label' => 'Alamat Kontak Darurat'],
            '[hubungan]'      => ['mode' => 'input', 'column' => 'relationship',     'label' => 'Hubungan Kontak Darurat'],

            // Variabel otomatis (diambil dari data karyawan/site/gaji; hanya referensi, diabaikan saat import).
            '[no_surat]'       => ['mode' => 'auto', 'column' => null, 'label' => 'No Surat'],
            '[tgl_surat]'      => ['mode' => 'auto', 'column' => null, 'label' => 'Tanggal Terbit'],
            '[romawi]'         => ['mode' => 'auto', 'column' => null, 'label' => 'Bulan Romawi'],
            '[tahun]'          => ['mode' => 'auto', 'column' => null, 'label' => 'Tahun'],
            '[sign_2]'         => ['mode' => 'auto', 'column' => null, 'label' => 'TTD Karyawan'],
            '[esign]'          => ['mode' => 'auto', 'column' => null, 'label' => 'TTD HRD'],
            '[nama_karyawan]'  => ['mode' => 'auto', 'column' => null, 'label' => 'Nama Karyawan'],
            '[no_karyawan]'    => ['mode' => 'auto', 'column' => null, 'label' => 'No Karyawan'],
            '[nik_ktp]'        => ['mode' => 'auto', 'column' => null, 'label' => 'NIK KTP'],
            '[jenis_kelamin]'  => ['mode' => 'auto', 'column' => null, 'label' => 'Jenis Kelamin'],
            '[ttl]'            => ['mode' => 'auto', 'column' => null, 'label' => 'Tempat/Tgl Lahir'],
            '[alamat]'         => ['mode' => 'auto', 'column' => null, 'label' => 'Alamat'],
            '[handphone]'      => ['mode' => 'auto', 'column' => null, 'label' => 'No HP'],
            '[jabatan]'        => ['mode' => 'auto', 'column' => null, 'label' => 'Jabatan'],
            '[lokasi_project]' => ['mode' => 'auto', 'column' => null, 'label' => 'Lokasi Project'],
            '[area]'           => ['mode' => 'auto', 'column' => null, 'label' => 'Area'],
            '[area_description]'=> ['mode' => 'auto','column' => null, 'label' => 'Deskripsi Area'],
            '[nama_client]'    => ['mode' => 'auto', 'column' => null, 'label' => 'Nama Client'],
            '[jabatan_client]' => ['mode' => 'auto', 'column' => null, 'label' => 'Jabatan Client'],
            '[gaji]'           => ['mode' => 'auto', 'column' => null, 'label' => 'Gaji'],
            '[tunjangan]'      => ['mode' => 'auto', 'column' => null, 'label' => 'Tunjangan'],
            '[komisi]'         => ['mode' => 'auto', 'column' => null, 'label' => 'Komisi'],
            '[potongan]'       => ['mode' => 'auto', 'column' => null, 'label' => 'Potongan'],
            '[gaji_pokok]'         => ['mode' => 'auto', 'column' => null, 'label' => 'Gaji Pokok'],
            '[tunj_jabatan]'       => ['mode' => 'auto', 'column' => null, 'label' => 'Tunj. Jabatan'],
            '[tunj_kehadiran]'     => ['mode' => 'auto', 'column' => null, 'label' => 'Tunj. Kehadiran'],
            '[tunj_komunikasi]'    => ['mode' => 'auto', 'column' => null, 'label' => 'Tunj. Komunikasi'],
            '[tunj_makan]'         => ['mode' => 'auto', 'column' => null, 'label' => 'Tunj. Makan'],
            '[tunj_transport]'     => ['mode' => 'auto', 'column' => null, 'label' => 'Tunj. Transport'],
            '[tunj_lembur_tetap]'  => ['mode' => 'auto', 'column' => null, 'label' => 'Tunj. Lembur Tetap'],
            '[tunj_other_non_fix]' => ['mode' => 'auto', 'column' => null, 'label' => 'Tunj. Other Non Fix'],
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
        foreach ($this->fixedVariableMap() as $token => $meta) {
            if (!$this->tokenUsed($content, $token)) {
                continue;
            }

            if ($meta['mode'] === 'input') {
                $columns[] = [
                    'type'  => 'fixed',
                    'label' => $meta['label'],
                    'key'   => $meta['column'],
                ];
            } else {
                // Variabel otomatis: kolom referensi (diisi otomatis saat export,
                // diabaikan saat import karena nilainya dari data karyawan).
                $columns[] = [
                    'type'  => 'auto',
                    'label' => $meta['label'] . ' (otomatis)',
                    'key'   => $token,
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

        // Normalisasi non-breaking space (&nbsp; -> 0xA0) dari editor teks
        // menjadi spasi biasa agar token seperti "[ mulai ]" tetap terdeteksi.
        $text = str_replace(["\xC2\xA0", "\xA0"], ' ', $text);

        return $text;
    }

    /**
     * Nilai referensi untuk kolom otomatis, diambil dari data karyawan/site/gaji.
     * Token yang baru terisi saat penerbitan (no_surat, ttd, dll) dikosongkan.
     */
    protected function resolveAutoValue(string $token, \App\Models\User $user): string
    {
        $profile = $user->profile;
        $site = $user->site;
        $setting = $user->salarySetting;

        $rupiah = fn ($v) => 'Rp ' . number_format((float) ($v ?? 0), 0, ',', '.');

        switch ($token) {
            case '[nama_karyawan]':  return (string) ($user->name ?? '');
            case '[no_karyawan]':    return (string) ($user->employee_nik ?? '');
            case '[nik_ktp]':        return (string) ($user->nik ?? '');
            case '[handphone]':      return (string) ($user->phone ?? '');
            case '[jenis_kelamin]':  return (string) (optional($profile)->gender ?? '');
            case '[alamat]':         return (string) (optional($profile)->address ?? '');
            case '[ttl]':
                if (optional($profile)->birth_place && optional($profile)->birth_date) {
                    return $profile->birth_place . ', ' . \Illuminate\Support\Carbon::parse($profile->birth_date)->format('d-m-Y');
                }
                return '';
            case '[jabatan]':        return (string) (optional($user->roles->first())->name ?? '');
            case '[lokasi_project]': return (string) (optional($site)->name ?? '');
            case '[area]':           return (string) (optional($site)->area ?? '');
            case '[area_description]':return (string) (optional($site)->description ?? '');
            case '[nama_client]':    return (string) (optional($site)->client_name ?? '');
            case '[jabatan_client]': return (string) (optional($site)->client_position ?? '');
            case '[gaji_pokok]':         return $rupiah(optional($setting)->gaji_pokok);
            case '[tunj_jabatan]':       return $rupiah(optional($setting)->tunj_jabatan);
            case '[tunj_kehadiran]':     return $rupiah(optional($setting)->tunj_kehadiran);
            case '[tunj_komunikasi]':    return $rupiah(optional($setting)->tunj_komunikasi);
            case '[tunj_makan]':         return $rupiah(optional($setting)->tunj_makan);
            case '[tunj_transport]':     return $rupiah(optional($setting)->tunj_transport);
            case '[tunj_lembur_tetap]':  return $rupiah(optional($setting)->tunj_lembur_tetap);
            case '[tunj_other_non_fix]': return $rupiah(optional($setting)->tunj_other_non_fix);
            default:
                // no_surat, tgl_surat, romawi, tahun, sign_2, esign, gaji, tunjangan,
                // komisi, potongan -> baru terisi saat penerbitan.
                return '';
        }
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
