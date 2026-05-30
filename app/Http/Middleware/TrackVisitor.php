<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Visitor;

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
        // Hanya track halaman utama
        if (!$request->is('/')) {
            return $next($request);
        }

        // Dapatkan informasi browser dan device dari User-Agent
        $userAgent = $request->userAgent() ?? '';
        $deviceInfo = $this->parseUserAgent($userAgent);

        // Simpan informasi pengunjung
        try {
            Visitor::create([
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent,
                'page_url' => $request->path(),
                'referrer' => $request->header('referer'),
                'method' => $request->method(),
                'browser' => $deviceInfo['browser'],
                'os' => $deviceInfo['os'],
                'device' => $deviceInfo['device'],
                'device_name' => $deviceInfo['device_name'],
                'visited_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Jika terjadi error, abaikan dan lanjutkan
        }

        return $next($request);
    }

    /**
     * Parse User-Agent string untuk mendapatkan info device
     */
    private function parseUserAgent($userAgent)
    {
        $browser = 'Unknown';
        $os = 'Unknown';
        $device = 'Desktop';
        $deviceName = 'Unknown';

        // Parsing Browser
        if (preg_match('/Chrome\/(\d+)/i', $userAgent, $match)) {
            $browser = 'Chrome ' . $match[1];
        } elseif (preg_match('/Firefox\/(\d+)/i', $userAgent, $match)) {
            $browser = 'Firefox ' . $match[1];
        } elseif (preg_match('/Safari\/(\d+)/i', $userAgent, $match) && !preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge\/(\d+)/i', $userAgent, $match)) {
            $browser = 'Edge ' . $match[1];
        } elseif (preg_match('/MSIE\s(\d+)/i', $userAgent, $match)) {
            $browser = 'IE ' . $match[1];
        }

        // Parsing Operating System
        if (preg_match('/Windows NT 10\.0/i', $userAgent)) {
            $os = 'Windows 10';
        } elseif (preg_match('/Windows NT 6\.3/i', $userAgent)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6\.2/i', $userAgent)) {
            $os = 'Windows 8';
        } elseif (preg_match('/Windows NT 6\.1/i', $userAgent)) {
            $os = 'Windows 7';
        } elseif (preg_match('/Mac OS X/i', $userAgent)) {
            $os = 'Mac OS X';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/iPhone/i', $userAgent)) {
            $os = 'iOS';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $os = 'Android';
        }

        // Parsing Device Type & Device Name
        if (preg_match('/iPhone/i', $userAgent)) {
            $device = 'Mobile';
            if (preg_match('/iPhone\s*([\d]+[,\d]*)/i', $userAgent, $match)) {
                $deviceName = 'iPhone ' . trim($match[1]);
            } else {
                $deviceName = 'iPhone';
            }
        } elseif (preg_match('/iPad/i', $userAgent)) {
            $device = 'Tablet';
            if (preg_match('/iPad\s*([\d]+[,\d]*)?/i', $userAgent, $match)) {
                $deviceName = 'iPad' . (isset($match[1]) ? ' ' . trim($match[1]) : '');
            } else {
                $deviceName = 'iPad';
            }
        } elseif (preg_match('/Android/i', $userAgent)) {
            // Check if tablet
            if (preg_match('/Tablet|SM-T|GT-P|RK3/i', $userAgent)) {
                $device = 'Tablet';
            } else {
                $device = 'Mobile';
            }
            
            if (preg_match('/Build\/([^\s;]+)/i', $userAgent, $match)) {
                $deviceName = $match[1];
            } elseif (preg_match('/SM-([^\s;]+)/i', $userAgent, $match)) {
                $deviceName = 'Samsung SM-' . $match[1];
            } else {
                $deviceName = $device === 'Tablet' ? 'Android Tablet' : 'Android Phone';
            }
        } elseif (preg_match('/Mobile|Nexus|HTC|Nokia|Blackberry/i', $userAgent)) {
            $device = 'Mobile';
            if (preg_match('/Nexus\s*([\d]+)/i', $userAgent, $match)) {
                $deviceName = 'Nexus ' . $match[1];
            } elseif (preg_match('/HTC\s*([^\s;]+)/i', $userAgent, $match)) {
                $deviceName = 'HTC ' . $match[1];
            } else {
                $deviceName = 'Mobile Device';
            }
        } else {
            $device = 'Desktop';
            if (preg_match('/Windows/i', $userAgent)) {
                $deviceName = 'Windows Computer';
            } elseif (preg_match('/Mac/i', $userAgent)) {
                $deviceName = 'Mac Computer';
            } elseif (preg_match('/Linux/i', $userAgent)) {
                $deviceName = 'Linux Computer';
            } else {
                $deviceName = 'Desktop Computer';
            }
        }

        return [
            'browser' => $browser,
            'os' => $os,
            'device' => $device,
            'device_name' => $deviceName,
        ];
    }
}
