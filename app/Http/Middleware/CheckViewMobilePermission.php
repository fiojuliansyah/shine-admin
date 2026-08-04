<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckViewMobilePermission
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && ! Auth::user()->can('view-mobile')) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}
