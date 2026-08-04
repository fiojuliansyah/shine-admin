<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckViewDesktopPermission
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && ! Auth::user()->can('view-desktop')) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses dashboard manage.');
        }

        return $next($request);
    }
}
