<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicantAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Gunakan guard khusus untuk applicant jika ada (opsional)
        if (!Auth::check()) {
            return redirect()->route('applicant-login');
        }

        return $next($request);
    }
}
