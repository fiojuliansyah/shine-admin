<?php

namespace App\Exports;

use App\Models\Site;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiteExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Site::with('company')->get()->map(function ($site) {
            return [
                'company_unique_id' => $site->company->unique_id ?? 'N/A',
                'name'              => $site->name,
                'description'        => $site->description,
                'lat'               => $site->lat,
                'long'              => $site->long,
                'radius'            => $site->radius,
                'client_name'       => $site->client_name,
                'client_phone'      => $site->client_phone,
                'client_email'      => $site->client_email,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID UNIK PERUSAHAAN',
            'NAMA',
            'DESCRIPTION',
            'LATITUDE',
            'LONGITUDE',
            'RADIUS',
            'NAMA CLIENT',
            'HANDPHONE CLIENT',
            'EMAIL CLIENT'
        ];
    }
}
