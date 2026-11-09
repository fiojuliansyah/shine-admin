<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Perbaikan data: proses Offering / Dokumen Digital dulu menyimpan tanggal akhir kontrak
 * ke profiles.resign_date sehingga karyawan baru langsung terbaca "Resign" dan hilang
 * dari daftar aktif. Pindahkan nilainya ke profiles.end_date.
 *
 * Hanya menyentuh profil yang resign_date-nya persis sama dengan end_date surat
 * "Auto generated from Bulk Offering" milik user yang sama, end_date masih kosong,
 * dan statusnya tidak ditandai RESIGN.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('profiles')
            ->whereNotNull('resign_date')
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', ''))
            ->where(fn ($q) => $q->whereNull('employment_status')->orWhere('employment_status', '!=', 'RESIGN'))
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('generates')
                    ->whereColumn('generates.user_id', 'profiles.user_id')
                    ->whereColumn('generates.end_date', 'profiles.resign_date')
                    ->where('generates.description', 'Auto generated from Bulk Offering');
            })
            ->update([
                'end_date' => DB::raw('resign_date'),
                'resign_date' => null,
            ]);
    }

    public function down(): void
    {
        // Tidak dibalik: data lama memang salah tempat.
    }
};
