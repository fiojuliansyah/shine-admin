<?php

namespace App\Exports;

use App\Models\Leave;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeaveExport implements FromCollection, WithHeadings
{
    protected $site_id;
    protected $start_date;
    protected $end_date;

    public function __construct($site_id, $start_date, $end_date)
    {
        $this->site_id = $site_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        $query = Leave::query();

        if ($this->site_id) {
            $query->where('site_id', $this->site_id);
        }

        if ($this->start_date && $this->end_date) {
            $query->whereBetween('start_date', [$this->start_date, $this->end_date]);
        }

        $leaves = $query->with(['site', 'user'])->get();

        $data = $leaves->map(function($leave) {
            return [
                'id' => $leave->id,
                'type' => $leave->type_id,
                'attendance_id' => $leave->attendance_id,
                'user_name' => $leave->user->name,
                'site_name' => $leave->site->name,
                'start_date' => $leave->start_date,
                'end_date' => $leave->end_date,
                'reason' => $leave->reason,
                'is_paid' => $leave->is_paid,
                'image_url' => $leave->image_url,
                'image_public_id' => $leave->image_public_id,
                'contact' => $leave->contact,
                'status' => $leave->status,
                'created_at' => $leave->created_at,
                'updated_at' => $leave->updated_at
            ];
        });

        return $data;
    }

    public function headings(): array
    {
        return [
            'ID', 
            'Tipe Cuti', 
            'ID Kehadiran', 
            'Nama Pengguna',  // Mengganti User Name dengan Nama Pengguna
            'Nama Lokasi',    // Mengganti Site Name dengan Nama Lokasi
            'Tanggal Mulai', 
            'Tanggal Selesai', 
            'Alasan', 
            'Status Bayar',   // Mengganti Paid dengan Status Bayar
            'URL Gambar', 
            'ID Gambar Publik', 
            'Kontak', 
            'Status', 
            'Tanggal Dibuat',
            'Tanggal Diperbarui',
        ];
    }
}
