<?php

namespace App\Imports;

use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
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
    
                $date = Carbon::createFromFormat('Y-m-d', "{$this->month}-" . ($key - 1)); 
    
                $shift = Shift::where('site_id', $user->site_id)
                ->where('shift_code', $shift_code)
                ->first();
    
                if ($shift) {
                    $schedules[] = [
                        'user_id'   => $user->id,
                        'site_id'   => $user->site_id ?? null,
                        'shift_id'   => $shift->id,
                        'date'      => $date,
                        'clock_in'  => $shift->clock_in,
                        'clock_out' => $shift->clock_out,
                        'type'      => $shift->type,
                        'late'      => $this->late,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
    
        // Simpan ke dalam database, upsert jika sudah ada
        Schedule::upsert($schedules, ['user_id', 'date'], ['clock_in', 'clock_out', 'type', 'late', 'updated_at']);
    }
    
    
}
