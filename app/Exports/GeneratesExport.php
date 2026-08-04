<?php

namespace App\Exports;

use App\Models\Generate;
use App\Models\CustomVariable;
use App\Models\TypeLetter;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GeneratesExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    protected ?string $startDate;
    protected ?string $endDate;
    protected $siteId;
    protected $typeId;
    protected $customVarColumns;

    public function __construct($startDate = null, $endDate = null, $siteId = null, $typeId = null)
    {
        $this->startDate = $startDate ?: null;
        $this->endDate = $endDate ?: null;
        $this->siteId = $siteId ?: null;
        $this->typeId = $typeId ?: null;
        $this->customVarColumns = $this->resolveCustomVariableColumns($this->typeId);
    }

    protected function resolveCustomVariableColumns($typeId = null)
    {
        $query = CustomVariable::query()
            ->select('variable', 'name')
            ->whereNotNull('variable')
            ->where('variable', '!=', '');

        if ($typeId === 'none') {
            $query->whereHas('letter', fn($q) => $q->whereNull('type_letter_id'));
        } elseif ($typeId) {
            $query->whereHas('letter', fn($q) => $q->where('type_letter_id', $typeId));
        }

        return $query->get()
            ->unique('variable')
            ->values()
            ->map(fn($cv) => [
                'variable' => $cv->variable,
                'name' => $cv->name ?: $cv->variable,
            ]);
    }

    protected function query()
    {
        return Generate::with([
                'letter.type',
                'site.company',
                'user.profile',
                'user.roles',
                'user.site.company',
                'valueVariables.customVariable',
            ])
            ->orderBy('created_at', 'DESC')
            ->when($this->siteId, fn($q) => $q->where('site_id', $this->siteId))
            ->when($this->typeId === 'none', function ($q) {
                $q->whereHas('letter', fn($sq) => $sq->whereNull('type_letter_id'));
            })
            ->when($this->typeId && $this->typeId !== 'none', function ($q) {
                $q->whereHas('letter', fn($sq) => $sq->where('type_letter_id', $this->typeId));
            })
            ->when($this->startDate && $this->endDate, function ($q) {
                $q->whereBetween('created_at', [
                    $this->startDate . ' 00:00:00',
                    $this->endDate . ' 23:59:59',
                ]);
            })
            ->when($this->startDate && !$this->endDate, function ($q) {
                $q->where('created_at', '>=', $this->startDate . ' 00:00:00');
            })
            ->when(!$this->startDate && $this->endDate, function ($q) {
                $q->where('created_at', '<=', $this->endDate . ' 23:59:59');
            });
    }

    public function array(): array
    {
        $rows = [];
        $no = 1;

        foreach ($this->query()->get() as $row) {
            $profile = $row->user->profile ?? null;
            $userSite = $row->user->site ?? null;
            $join = $profile->join_date ?? null;

            $data = [
                $no++,
                $row->formatted_letter_number ?? '-',
                $row->user->employee_nik ? "'" . $row->user->employee_nik : '-',
                $userSite->company->name ?? $row->site->company->name ?? '-',
                $row->created_at ? date('d', strtotime($row->created_at)) : '-',
                $row->created_at ? Carbon::parse($row->created_at)->locale('id')->translatedFormat('F') : '-',
                $row->created_at ? date('Y', strtotime($row->created_at)) : '-',
                $row->user->name ?? '-',
                $row->user->email ?? '-',
                $profile->mother_name ?? '-',
                $profile->birth_place ?? '-',
                $profile->birth_date ?? '-',
                $profile->gender ?? '-',
                $row->user->nik ? "'" . $row->user->nik : '-',
                $profile?->kk_number ? "'" . $profile->kk_number : '-',
                $profile?->npwp_number ? "'" . $profile->npwp_number : '-',
                $this->formatAddress($profile),
                $row->user->phone ? "'" . $row->user->phone : '-',
                $profile->religion ?? '-',
                $profile->marriage_status ?? '-',
                $row->user && $row->user->roles->isNotEmpty() ? $row->user->roles->pluck('name')->implode(', ') : '-',
                $userSite->name ?? $row->site->name ?? '-',
                $userSite->client_name ?? $row->site->client_name ?? '-',
                $userSite->client_position ?? $row->site->client_position ?? '-',
                $join ? date('d', strtotime($join)) : '-',
                $join ? Carbon::parse($join)->locale('id')->translatedFormat('F') : '-',
                $join ? date('Y', strtotime($join)) : '-',
                $profile->bank_name ?? '-',
                $profile->account_name ?? '-',
                $profile?->account_number ? "'" . $profile->account_number : '-',
            ];

            foreach ($this->customVarColumns as $col) {
                $value = $row->valueVariables
                    ->first(fn($cv) => $cv->customVariable && $cv->customVariable->variable === $col['variable']);
                $data[] = $value->value ?? '-';
            }

            $rows[] = $data;
        }

        return $rows;
    }

    protected function formatAddress($profile): string
    {
        if (!$profile) {
            return '-';
        }
        $parts = array_filter([
            $profile->address ?? null,
            $profile->rt_rw ?? null,
            $profile->kelurahan ? 'Kel. ' . $profile->kelurahan : null,
            $profile->kecamatan ? 'Kec. ' . $profile->kecamatan : null,
        ]);
        return implode(', ', $parts) ?: '-';
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'No Surat',
            'NIK Karyawan',
            'Nama Perusahaan',
            'Tgl Pembuatan',
            'Bulan Pembuatan',
            'Tahun Pembuatan',
            'Nama',
            'Email',
            'Ibu Kandung',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'No KTP',
            'No KK',
            'No NPWP',
            'Alamat KTP',
            'No Handphone',
            'Agama',
            'Status Pernikahan',
            'Jabatan',
            'Site Project',
            'Nama Client',
            'Jabatan Client',
            'Tgl Join',
            'Bulan Join',
            'Tahun Join',
            'Nama Bank',
            'Nama Rekening',
            'No Rekening',
        ];

        foreach ($this->customVarColumns as $col) {
            $headings[] = $col['name'];
        }

        return $headings;
    }

    public function title(): string
    {
        return 'Surat Terbit';
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
