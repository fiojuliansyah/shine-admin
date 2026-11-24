<?php

namespace App\Http\Middleware;

use App\Support\ApplicantRequirements;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pelamar wajib menyelesaikan wizard "Lengkapi Profil" (data + dokumen wajib)
 * sebelum bisa membuka dashboard / melamar. Pegawai tidak terkena aturan ini
 * karena portal yang sama dipakai pegawai (mis. tanda tangan surat).
 */
class EnsureApplicantProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Pegawai & pengguna admin (punya akses dashboard manage) tidak diwajibkan.
        $exempt = ! $user || $user->is_employee || $user->can('view-desktop');

        if (! $exempt && ! ApplicantRequirements::isComplete($user)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Lengkapi profil Anda terlebih dahulu.'], 403);
            }

            return redirect()->route('applicant.onboarding')
                ->with('error', 'Lengkapi data profil dan dokumen Anda terlebih dahulu sebelum melamar.');
        }

        return $next($request);
    }
}
