<?php

namespace App\Exports;

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
    protected array $columns;

    public function __construct(Letter $letter)
    {
        $this->letter = $letter->loadMissing('customVariables');
        $this->columns = $this->buildTemplateColumns($this->letter);
    }

    public function array(): array
    {
        return [];
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
