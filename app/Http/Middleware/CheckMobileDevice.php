<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class CheckMobileDevice
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Initialize Agent
        $agent = new Agent;

        // Check if the request is coming from a mobile device
        if (($agent->isMobile() || $agent->isTablet()) && ($request->is('dashboard') || $request->is('manage/*'))) {
            abort(403, 'Dashboard manage tidak tersedia untuk perangkat mobile.');
        }

        return $next($request);
    }
}
