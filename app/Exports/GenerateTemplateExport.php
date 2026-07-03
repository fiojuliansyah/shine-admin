<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Letter;
use App\Traits\GenerateTemplateColumns;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenerateTemplateExport implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    use GenerateTemplateColumns;

    protected Letter $letter;
    protected $siteId;
    protected bool $withEmployees;
    protected array $columns;

    public function __construct(Letter $letter, $siteId = null, bool $withEmployees = true)
    {
        $this->letter = $letter->loadMissing('customVariables');
        $this->siteId = $siteId ?: null;
        $this->withEmployees = $withEmployees;
        $this->columns = $this->buildTemplateColumns($this->letter);
    }

    public function array(): array
    {
        if (!$this->withEmployees) {
            return [];
        }

        $query = User::with(['profile', 'site', 'roles', 'salarySetting'])
            ->where('is_employee', 1)
            ->orderBy('name');

        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }

        return $query->get()->map(function ($user) {
            return array_map(function ($col) use ($user) {
                return match ($col['type']) {
                    'nik'  => (string) ($user->employee_nik ?? ''),
                    'auto' => $this->resolveAutoValue($col['key'], $user),
                    default => '',
                };
            }, $this->columns);
        })->values()->toArray();
    }

    public function headings(): array
    {
        return array_map(fn ($col) => $col['label'], $this->columns);
    }

    public function title(): string
    {
        return 'Template Surat';
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
