<?php

namespace App\Imports;

use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ScheduleImport implements ToCollection, WithStartRow
{
    protected $month;
    protected $late;

    public function __construct($month, $late)
    {
        $this->month = $month;
        $this->late = $late;
    }

    public function startRow(): int
    {
        return 3; 
    }

    public function collection(Collection $rows)
    {
        $schedules = [];
        $attendances = [];
    
        foreach ($rows as $row) {
            $no_karyawan = trim($row[0] ?? '');
            $nama = trim($row[1] ?? '');
    
            if (empty($no_karyawan) || empty($nama)) {
                continue; 
            }
    
            $user = User::where('name', $nama)->orWhere('employee_nik', $no_karyawan)->first();
    
            if (!$user) {
                continue; 
            }
    
            foreach ($row as $key => $shift_code) {
                if ($key < 2 || empty($shift_code)) {
                    continue; 
                }
    
                $day = $key - 1;
                $dateString = "{$this->month}-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                $date = Carbon::parse($dateString);
    
                $shift = Shift::where('site_id', $user->site_id)
                    ->where('shift_code', $shift_code)
                    ->first();
    
                if ($shift) {
                    $shiftType = strtolower($shift->type);

                    $schedules[] = [
                        'user_id'    => $user->id,
                        'site_id'    => $user->site_id,
                        'shift_id'   => $shift->id,
                        'date'       => $date->toDateString(),
                        'clock_in'   => $shift->clock_in,
                        'clock_out'  => $shift->clock_out,
                        'type'       => $shift->type,
                        'late'       => $this->late,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if ($shiftType === 'off' || $shiftType === 'leave') {
                        $attendances[] = [
                            'user_id'    => $user->id,
                            'site_id'    => $user->site_id,
                            'date'       => $date->toDateString(),
                            'type'       => $shiftType,
                            'clock_in'   => null,
                            'clock_out'  => null,
                            'latlong'    => null,
                            'remark'     => 'Auto-generated from Schedule Import (' . strtoupper($shiftType) . ')',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }
    
        if (!empty($schedules)) {
            Schedule::upsert($schedules, ['user_id', 'date'], ['shift_id', 'site_id', 'clock_in', 'clock_out', 'type', 'late', 'updated_at']);
        }

        if (!empty($attendances)) {
            Attendance::upsert($attendances, ['user_id', 'date'], ['site_id', 'type', 'remark', 'updated_at']);
        }
    }
}