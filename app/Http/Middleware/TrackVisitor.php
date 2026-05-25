<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Visitor;
use Jenssegers\Agent\Agent;

class TrackVisitor
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
        // Jangan track request API
        if ($request->is('api/*') || $request->is('sync-*') || $request->is('debug-*')) {
            return $next($request);
        }

        // Dapatkan informasi browser dan device
        $agent = new Agent();
        $browser = $agent->browser() ?? 'Unknown';
        $os = $agent->platform() ?? 'Unknown';
        $device = 'Desktop';
        $deviceName = 'Unknown';
        
        if ($agent->isPhone()) {
            $device = 'Mobile';
            $deviceName = $agent->device() ?? 'Mobile Device';
        } elseif ($agent->isTablet()) {
            $device = 'Tablet';
            $deviceName = $agent->device() ?? 'Tablet Device';
        } else {
            $deviceName = $agent->device() ?? 'Desktop Computer';
        }

        // Simpan informasi pengunjung
        try {
            Visitor::create([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'page_url' => $request->path(),
                'referrer' => $request->referrer(),
                'method' => $request->method(),
                'browser' => $browser,
                'os' => $os,
                'device' => $device,
                'device_name' => $deviceName,
                'visited_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Jika terjadi error, abaikan dan lanjutkan
        }

        return $next($request);
    }
}
