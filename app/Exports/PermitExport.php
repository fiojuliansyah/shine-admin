<?php

namespace App\Exports;

use App\Models\Permit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PermitExport implements FromCollection, WithHeadings
{
    protected $site_id;
    protected $start_date;
    protected $end_date;

    // Constructor untuk menerima filter yang diterima dari form
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
        // Mengambil data permisi berdasarkan filter
        $query = Permit::query();

        // Filter berdasarkan site_id jika tersedia
        if ($this->site_id) {
            $query->where('site_id', $this->site_id);
        }

        // Filter berdasarkan rentang tanggal
        if ($this->start_date && $this->end_date) {
            $query->whereBetween('start_date', [$this->start_date, $this->end_date]);
        }

        // Mengambil data dan eager load site dan user
        $permits = $query->with(['site', 'user'])->get();

        // Menyusun data untuk ekspor
        return $permits->map(function($permit) {
            return [
                'id' => $permit->id,
                'title' => $permit->title,
                'attendance_id' => $permit->attendance_id,
                'user_name' => $permit->user->name, // Mendapatkan nama pengguna
                'site_name' => $permit->site->name, // Mendapatkan nama site
                'start_date' => $permit->start_date,
                'end_date' => $permit->end_date,
                'reason' => $permit->reason,
                'is_paid' => $permit->is_paid,
                'image_url' => $permit->image_url,
                'image_public_id' => $permit->image_public_id,
                'contact' => $permit->contact,
                'status' => $permit->status,
                'created_at' => $permit->created_at,
                'updated_at' => $permit->updated_at
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
            'Judul', 
            'ID Kehadiran', 
            'Nama Pengguna',  // Menambahkan nama pengguna
            'Nama Lokasi',    // Menambahkan nama lokasi
            'Tanggal Mulai', 
            'Tanggal Selesai', 
            'Alasan', 
            'Status Bayar',   // Mengganti Paid dengan Status Bayar
            'URL Gambar', 
            'ID Gambar Publik', 
            'Kontak', 
            'Status', 
            'Tanggal Dibuat', // Mengganti Created At dengan Tanggal Dibuat
            'Tanggal Diperbarui' // Mengganti Updated At dengan Tanggal Diperbarui
        ];
    }
}
