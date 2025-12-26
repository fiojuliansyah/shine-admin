<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckViewDesktopPermission
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->can('view-desktop')) {
            return redirect()->route('mobile.home');
        }

        return $next($request);
    }
}
