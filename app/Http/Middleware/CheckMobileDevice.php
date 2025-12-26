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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Initialize Agent
        $agent = new Agent();
        
        // Check if the request is coming from a mobile device
        if ($agent->isMobile() || $agent->isTablet()) {
            if ($request->is('dashboard') || $request->is('manage/*')) {
                return redirect()->route('mobile.home');
            }
        }

        return $next($request);
    }
}