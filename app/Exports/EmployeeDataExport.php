<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export data pegawai (juga dipakai sebagai template import, $template = true).
 *
 * - FromQuery: data diambil per chunk dengan kolom secukupnya (bukan seluruh profil
 *   yang berisi e-sign / face descriptor / QR) agar export semua site / per PT tidak berat.
 * - Kolom nomor identitas (NIK, KK, NPWP, rekening, BPJS, HP) ditulis sebagai TEKS:
 *   Excel hanya menyimpan 15 digit angka, sehingga digit ke-16 NIK berubah jadi 0.
 */
class EmployeeDataExport extends DefaultValueBinder implements FromQuery, WithColumnFormatting, WithColumnWidths, WithCustomValueBinder, WithHeadings, WithMapping, WithStyles
{
    /** Kolom yang selalu teks: NIK Karyawan, HP, NIK KTP, NPWP, KK, No Rekening, BPJS TK, BPJS KES. */
    private const TEXT_COLUMNS = ['C', 'F', 'N', 'O', 'P', 'Y', 'AB', 'AC'];

    private const PROFILE_COLUMNS = [
        'user_id', 'employment_status', 'mother_name', 'birth_place', 'birth_date', 'gender',
        'npwp_number', 'no_kk', 'address', 'religion', 'marriage_status', 'manager',
        'join_date', 'end_date', 'mutation_date', 'resign_date',
        'account_number', 'bank_name', 'account_name', 'bpjs_tk_number', 'bpjs_kes_number', 'keterangan',
    ];

    /**
     * @param  string|null  $status  'aktif' | 'resign' | null (semua)
     */
    public function __construct(
        private readonly ?int $siteId = null,
        private readonly bool $template = false,
        private readonly ?int $companyId = null,
        private readonly ?string $status = null,
    ) {}

    public function query(): Builder
    {
        return User::query()
            ->select(['users.id', 'users.name', 'users.email', 'users.phone', 'users.nik', 'users.employee_nik', 'users.site_id'])
            ->with([
                'profile' => fn ($q) => $q->select(self::PROFILE_COLUMNS),
                'site:id,name,area,company_id',
                'site.company:id,short_name',
                'roles:id,name,code',
            ])
            ->where('is_employee', 1)
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'App Administrator'))
            ->when($this->template, fn ($q) => $q->whereRaw('1 = 0'))
            ->when($this->siteId, fn ($q) => $q->where('users.site_id', $this->siteId))
            ->when($this->companyId, fn ($q) => $q->whereHas('site', fn ($s) => $s->where('company_id', $this->companyId)))
            ->when($this->status === 'aktif', fn ($q) => $q->activeEmployee())
            ->when($this->status === 'resign', fn ($q) => $q->resigned())
            ->orderBy('users.name')
            ->orderBy('users.id');
    }

    /**
     * @param  User  $user
     */
    public function map($user): array
    {
        $profile = $user->profile;
        $date = fn ($value) => format_tanggal($value, '');
        $role = $user->roles->first();

        return [
            $profile->isResigned() ? 'RESIGN' : 'ACTIVE',
            $user->site?->company?->short_name,
            $user->employee_nik,
            $user->name,
            $user->email,
            $user->phone,
            $profile->mother_name,
            $role?->code ?? $role?->name,
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
            $profile->manager,
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

    /** Nilai di kolom identitas dipaksa string agar tidak dikonversi ke angka oleh Excel. */
    public function bindValue(Cell $cell, mixed $value): bool
    {
        if ($value !== null && $value !== '' && in_array($cell->getColumn(), self::TEXT_COLUMNS, true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    /** Format teks juga untuk baris kosong, supaya angka yang diketik di template tidak terpotong. */
    public function columnFormats(): array
    {
        return collect(self::TEXT_COLUMNS)
            ->mapWithKeys(fn ($column) => ["{$column}:{$column}" => NumberFormat::FORMAT_TEXT])
            ->all();
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 10, 'C' => 18, 'D' => 28, 'E' => 28, 'F' => 16, 'G' => 24, 'H' => 18,
            'I' => 24, 'J' => 18, 'K' => 16, 'L' => 13, 'M' => 13, 'N' => 20, 'O' => 22, 'P' => 20,
            'Q' => 40, 'R' => 12, 'S' => 16, 'T' => 18, 'U' => 13, 'V' => 13, 'W' => 13, 'X' => 13,
            'Y' => 20, 'Z' => 16, 'AA' => 24, 'AB' => 18, 'AC' => 18, 'AD' => 30,
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
