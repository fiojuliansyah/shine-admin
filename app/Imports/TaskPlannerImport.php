<?php

namespace App\Imports;

use App\Models\TaskPlanner;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TaskPlannerImport implements ToModel, WithHeadingRow
{
    protected $site_id, $month;

    public function __construct($site_id, $month)
    {
        $this->site_id = $site_id;
        $this->month = $month; // format: YYYY-MM
    }

    public function model(array $row)
    {
        $tasks = [];

        // Ambil tanggal awal dari Excel
        $excelDay = Date::excelToDateTimeObject($row['date'])->format("d");

        // Tentukan start dan end date menggunakan Carbon
        $startDate = Carbon::createFromFormat('Y-m-d', $this->month . '-' . $excelDay);
        $endDate   = Carbon::createFromFormat('Y-m-d', date("Y-m-t", strtotime($this->month)));

        // Loop setiap hari dari startDate sampai endDate
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $tasks[] = new TaskPlanner([
                'service_type' => $row['service_type'] ?? null,
                'work_type'    => $row['work_type'] ?? null,
                'site_id'      => $this->site_id,
                'date'         => $date->format('Y-m-d'),
                'name'         => $row['name'] ?? null,
                'floor'        => $row['floor'] ?? null,
                'start_time'   => isset($row['start_time'])
                                    ? Date::excelToDateTimeObject($row['start_time'])->format("H:i:s")
                                    : null,
            ]);
        }

        return $tasks; // kembalikan array
    }
}
