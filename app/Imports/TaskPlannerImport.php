<?php

namespace App\Imports;

use App\Models\TaskPlanner;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TaskPlannerImport implements ToCollection
{
    protected $site_id, $month;

    public function __construct($site_id, $month)
    {
        $this->site_id = $site_id;
        $this->month   = $month;
    }

    public function collection(Collection $rows)
    {
        $startMonth = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $endMonth   = $startMonth->copy()->endOfMonth();

        foreach ($rows as $index => $row) {

            if ($index === 0) {
                continue;
            }

            $program = trim($row[1] ?? null);
            if (!$program) {
                continue;
            }

            $tanggal = $row[3] ?? null;
            $hari    = $row[4] ?? null;

            if ($tanggal) {

                $date = Carbon::parse($tanggal);

                if ($date->format('Y-m') === $this->month) {
                    $this->createTask($program, $date);
                }

            } elseif ($hari) {

                $targetDay = strtolower(trim($hari));

                for ($date = $startMonth->copy(); $date->lte($endMonth); $date->addDay()) {
                    if (strtolower($date->locale('id')->dayName) === $targetDay) {
                        $this->createTask($program, $date);
                    }
                }

            } else {

                for ($date = $startMonth->copy(); $date->lte($endMonth); $date->addDay()) {
                    $this->createTask($program, $date);
                }
            }
        }
    }

    private function createTask(string $name, Carbon $date): void
    {
        TaskPlanner::create([
            'site_id' => $this->site_id,
            'name'    => $name,
            'date'    => $date->format('Y-m-d'),
            'status'  => 'pending',
        ]);
    }
}
