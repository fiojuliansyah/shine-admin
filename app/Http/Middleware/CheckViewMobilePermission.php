<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckViewMobilePermission
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->can('view-mobile')) {
            return redirect('/mobile/login');
        }

        return $next($request);
    }
}
