<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class EmployeeAttendanceExport implements FromCollection, WithHeadings, WithEvents
{
    protected $userId;
    protected $startDate;
    protected $endDate;
    protected $siteId;

    public function __construct($userId, $siteId, $startDate, $endDate)
    {
        $this->userId = $userId;
        $this->siteId = $siteId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Attendance::with(['site', 'overtimes', 'minutes'])
            ->whereBetween('date', [$this->startDate, $this->endDate]);
    
        // Tambahkan filter untuk user_id jika ada
        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }
    
        // Tambahkan filter untuk site_id jika ada
        if ($this->siteId) {
            $query->where('site_id', $this->siteId);
        }
    
        return $query->get()->map(function ($attendance) {
            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);
            $totalMinutes = $clockIn->diffInMinutes($clockOut);
    
            $overtimeMinutes = 0;
            if ($attendance->overtime) {
                $overtimeClockIn = Carbon::parse($attendance->overtime->clock_in);
                $overtimeClockOut = Carbon::parse($attendance->overtime->clock_out);
                $overtimeMinutes = $overtimeClockIn->diffInMinutes($overtimeClockOut);
            }
    
            $indicator = $attendance->minutes->isNotEmpty() ? 'Absen Menggunakan Berita Acara' : '';
    
            return [
                'Date' => $attendance->date,
                'Clock In' => $attendance->clock_in,
                'Clock Out' => $attendance->clock_out,
                'LatLong' => $attendance->latlong,
                'Site' => $attendance->site ? $attendance->site->name : 'N/A',
                'Overtime (minutes)' => $overtimeMinutes,
                'Indicator' => $indicator,
            ];
        });
    }
    

    public function headings(): array
    {
        return [
            'Date',
            'Clock In',
            'Clock Out',
            'LatLong',
            'Site',
            'Overtime (minutes)',
            'Indicator',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getDelegate()->getHighestRow();
                
                $data = $this->collection();
                $totalMinutes = 0;
                $indicatorCount = 0;
                $totalOvertimeMinutes = 0;

                $uniqueDates = $data->pluck('Date')->unique();

                foreach ($data as $row) {
                    $totalMinutes += $row['Clock In'] && $row['Clock Out'] ? Carbon::parse($row['Clock In'])->diffInMinutes(Carbon::parse($row['Clock Out'])) : 0;
                    $indicatorCount += $row['Indicator'] ? 1 : 0;
                    $totalOvertimeMinutes += $row['Overtime (minutes)'];
                }

                $totalHours = floor($totalMinutes / 60);
                $totalMinutesRemainder = $totalMinutes % 60;

                $overtimeHours = floor($totalOvertimeMinutes / 60);
                $overtimeMinutes = $totalOvertimeMinutes % 60;

                $footerRowStart = $highestRow + 1;
                $sheet->getDelegate()->setCellValue('A' . $footerRowStart, 'Total Waktu Kerja');
                $sheet->getDelegate()->setCellValue('B' . $footerRowStart, "{$totalHours} Jam {$totalMinutesRemainder} Menit");

                $sheet->getDelegate()->setCellValue('A' . ($footerRowStart + 1), 'Total Penggunaan Indicator');
                $sheet->getDelegate()->setCellValue('B' . ($footerRowStart + 1), $indicatorCount);

                $sheet->getDelegate()->setCellValue('A' . ($footerRowStart + 2), 'Total Overtime');
                $sheet->getDelegate()->setCellValue('B' . ($footerRowStart + 2), "{$overtimeHours} Jam {$overtimeMinutes} Menit");

                // Add total workdays
                $sheet->getDelegate()->setCellValue('A' . ($footerRowStart + 3), 'Total Hari Kerja');
                $sheet->getDelegate()->setCellValue('B' . ($footerRowStart + 3), $uniqueDates->count());
            },
        ];
    }
}
