<?php

namespace App\Imports;

use App\Models\Floor;
use Illuminate\Support\Collection;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Maatwebsite\Excel\Concerns\ToCollection;

class FloorsImport implements ToCollection
{
    protected $siteId;

    public function __construct($siteId)
    {
        $this->siteId = $siteId;
    }

    public function collection(Collection $rows)
    {
        foreach($rows as $index => $row){
            if ($index === 0) continue;

            if ($row->filter()->isEmpty()) continue;

            $floor = Floor::create([
                'site_id'     => $this->siteId,
                'name'        => $row[0],
                'description' => $row[1] ?? null,
            ]);

            $url = 'https://app.gamaintegratedsystem.com/mobile/floor/' . $floor->id . '/patroll';
            $qrCode = QrCode::size(200)->generate($url);

            $floor->floor_qr = $qrCode;
            $floor->save();
        }
    }
}
