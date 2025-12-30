<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Generate;
use App\Models\Payroll;
use App\Models\PayrollComponent;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CombinedImport implements ToCollection
{
    protected $template;
    protected $site;
    protected $startDate;
    protected $endDate;

    public function __construct($template, $site, $startDate, $endDate)
    {
        $this->template = $template;
        $this->site = $site;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection(Collection $collection)
    {
        $collection = $collection->skip(1); 

        foreach ($collection as $row) {
            if ($row->isEmpty()) {
                continue;
            }

            $employeeNik = $row[5];
            $employee = User::where('employee_nik', $employeeNik)->first();

            if (!$employee) {
                continue;
            }

            $generate = Generate::create([
                'letter_id' => $this->template,
                'letter_number' => $row[0],
                'romawi' => $row[1],
                'year' => $row[2],
                'day' => $row[3],
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'second_party' => $row[4],
                'user_id' => $employee->id,
                'site_id' => $this->site,
            ]);

            $payroll = Payroll::create([
                'user_id' => $employee->id,
                'site_id' => $this->site,
                'pay_type' => $row[6],
                'salary_amount' => $row[7],
                'daily_rate' => $row[8],
                'cutoff_day' => $row[9] ?? 20,
            ]);

            $componentType = $row[10];
            $componentName = $row[11];
            $componentAmount = $row[12];
            $componentPercentage = $row[13];

            if ($componentType) {
                PayrollComponent::create([
                    'payroll_id' => $payroll->id,
                    'component_type' => $componentType,
                    'name' => $componentName,
                    'amount' => $componentAmount,
                    'percentage' => $componentPercentage,
                ]);
            }
        }
    }
}
