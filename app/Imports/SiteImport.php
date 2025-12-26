<?php

namespace App\Imports;

use App\Models\Site;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SiteImport implements ToCollection
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            if ($row->filter()->isEmpty()) {
                continue;
            }

            Site::create([
                'company_id'   => $this->companyId,
                'name'         => $row[0] ?? null,  
                'description'  => $row[1] ?? null,
                'lat'          => $row[2] ?? null,        
                'long'         => $row[3] ?? null,
                'radius'       => $row[4] ?? null,
                'client_name'  => $row[5] ?? null,
                'client_phone' => $row[6] ?? null,
                'client_email' => $row[7] ?? null, 
            ]);
        }
    }

}
