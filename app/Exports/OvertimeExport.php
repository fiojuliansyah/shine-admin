<?php

namespace App\Exports;

use App\Models\Overtime;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OvertimeExport implements FromCollection, WithHeadings
{
    protected $start_date;
    protected $end_date;
    protected $site_id;

    // Constructor untuk menerima filter
    public function __construct($site_id, $start_date, $end_date)
    {
        $this->site_id = $site_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Overtime::query();

        // Filter berdasarkan site_id jika ada
        if ($this->site_id) {
            $query->whereHas('attendance', function ($query) {
                $query->where('site_id', $this->site_id);
            });
        }

        // Filter berdasarkan rentang tanggal jika ada
        if ($this->start_date && $this->end_date) {
            $query->whereBetween('created_at', [$this->start_date, $this->end_date]);
        }

        // Eager load attendance dan site untuk mendapatkan data terkait
        $overtimes = $query->with(['attendance.site'])->get();

        // Menyusun data yang akan diekspor
        return $overtimes->map(function($overtime) {
            return [
                'id' => $overtime->id,
                'attendance_id' => $overtime->attendance_id,
                'clock_in' => $overtime->clock_in,
                'clock_out' => $overtime->clock_out,
                'reason' => $overtime->reason,
                'backup_id' => $overtime->backup_id,
                'demand' => $overtime->demand,
                'site_name' => $overtime->attendance->site->name, // Menambahkan nama site
                'status' => $overtime->status,
                'created_at' => $overtime->created_at,
                'updated_at' => $overtime->updated_at
            ];
        });
    }

    /**
     * Menambahkan heading dalam bahasa Indonesia
     */
    public function headings(): array
    {
        return [
            'ID', 
            'ID Kehadiran', 
            'Jam Masuk', 
            'Jam Keluar', 
            'Alasan', 
            'Backup ID', 
            'Permintaan', 
            'Nama Lokasi',
            'Status', 
            'Tanggal Dibuat', 
            'Tanggal Diperbarui'
        ];
    }
}

