<?php

namespace App\Services;

use App\Models\Generate;
use App\Models\Mutation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Revisi periode kontrak pada surat terbit (mis. PKWT) yang tanggalnya salah.
 * Dipakai revisi per karyawan (halaman detail pegawai) dan revisi massal (Surat Terbit).
 *
 * - Surat dirender dari start_date/end_date, jadi PDF/print langsung memakai periode baru.
 * - Nomor surat & tanda tangan tidak berubah.
 * - Profil karyawan (join_date / end_date) ikut diperbarui bila surat ini kontrak terbarunya.
 * - Dicatat di Riwayat Karier (jenis "kontrak").
 */
class ContractRevision
{
    public static function apply(Generate $generate, string $startDate, ?string $endDate, ?string $description = null): void
    {
        DB::transaction(function () use ($generate, $startDate, $endDate, $description) {
            $oldStart = $generate->start_date;
            $oldPeriod = self::period($generate->start_date, $generate->end_date);

            // Tanda tangan HRD & karyawan TIDAK direset (sesuai kebijakan: cukup periode yang dikoreksi).
            $generate->update(['start_date' => $startDate, 'end_date' => $endDate]);

            $user = $generate->user()->with('profile')->first();
            if (! $user) {
                return;
            }

            // Profil hanya mengikuti kontrak TERBARU karyawan (bukan kontrak lama yang direvisi).
            $latestContractId = Generate::where('user_id', $user->id)
                ->whereNotNull('start_date')
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->value('id');

            if ((int) $latestContractId === (int) $generate->id) {
                $profile = ['end_date' => $endDate];
                if (blank($user->profile?->join_date) || $user->profile->join_date === $oldStart) {
                    $profile['join_date'] = $startDate;
                }
                $user->profile()->updateOrCreate(['user_id' => $user->id], $profile);
            }

            Mutation::create([
                'type' => 'kontrak',
                'date' => now()->toDateString(),
                'user_id' => $user->id,
                'generate_id' => $generate->id,
                'description' => $description,
                'created_by' => Auth::id(),
                'from_value' => $oldPeriod,
                'to_value' => self::period($startDate, $endDate),
            ]);
        });
    }

    public static function period($start, $end): string
    {
        return format_tanggal($start) . ' s/d ' . format_tanggal($end, 'selesai');
    }
}
