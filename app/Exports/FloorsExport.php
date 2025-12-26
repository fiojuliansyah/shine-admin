<?php

namespace App\Exports;

use App\Models\floor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FloorsExport implements FromCollection, WithHeadings
{
    public function collection()
    {

        return Floor::with('site')->get()->map(function ($floor) {
            return [
                'site' => $floor->site->name,
                'name' => $floor->name,
                'description' => $floor->description,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'site',
            'name',
            'description',
        ];
    }
}
