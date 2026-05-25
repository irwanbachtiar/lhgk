<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitorController extends Controller
{
    /**
     * Menampilkan halaman pantau pengunjung
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'today'); // today, week, month, all

        // Dapatkan data visitor berdasarkan periode
        $visitors = $this->getVisitorsByPeriod($period);

        // Statistik umum
        $stats = $this->getStatistics($period);

        // Data untuk chart
        $chartData = $this->getChartData($period);

        // Top pages
        $topPages = Visitor::query()
            ->when($period !== 'all', function ($query) use ($period) {
                return $this->applyPeriodFilter($query, $period);
            })
            ->select('page_url', DB::raw('COUNT(*) as total'))
            ->whereNotNull('page_url')
            ->groupBy('page_url')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Browser distribution
        $browserDistribution = Visitor::query()
            ->when($period !== 'all', function ($query) use ($period) {
                return $this->applyPeriodFilter($query, $period);
            })
            ->select('browser', DB::raw('COUNT(*) as total'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderBy('total', 'desc')
            ->get();

        // Device distribution
        $deviceDistribution = Visitor::query()
            ->when($period !== 'all', function ($query) use ($period) {
                return $this->applyPeriodFilter($query, $period);
            })
            ->select('device', DB::raw('COUNT(*) as total'))
            ->whereNotNull('device')
            ->groupBy('device')
            ->orderBy('total', 'desc')
            ->get();

        // OS distribution
        $osDistribution = Visitor::query()
            ->when($period !== 'all', function ($query) use ($period) {
                return $this->applyPeriodFilter($query, $period);
            })
            ->select('os', DB::raw('COUNT(*) as total'))
            ->whereNotNull('os')
            ->groupBy('os')
            ->orderBy('total', 'desc')
            ->get();

        // Hourly traffic
        $hourlyTraffic = Visitor::query()
            ->when($period !== 'all', function ($query) use ($period) {
                return $this->applyPeriodFilter($query, $period);
            })
            ->select(DB::raw('HOUR(visited_at) as hour'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('HOUR(visited_at)'))
            ->orderBy('hour')
            ->get();

        // Latest visitors
        $latestVisitors = Visitor::query()
            ->when($period !== 'all', function ($query) use ($period) {
                return $this->applyPeriodFilter($query, $period);
            })
            ->orderBy('visited_at', 'desc')
            ->paginate(50);

        return view('visitors.monitor', compact(
            'stats',
            'chartData',
            'topPages',
            'browserDistribution',
            'deviceDistribution',
            'osDistribution',
            'hourlyTraffic',
            'latestVisitors',
            'period'
        ));
    }

    /**
     * Get visitors berdasarkan periode
     */
    private function getVisitorsByPeriod($period)
    {
        $query = Visitor::query();

        return match($period) {
            'today' => $query->today(),
            'week' => $query->thisWeek(),
            'month' => $query->thisMonth(),
            default => $query,
        };
    }

    /**
     * Apply period filter ke query
     */
    private function applyPeriodFilter($query, $period)
    {
        return match($period) {
            'today' => $query->today(),
            'week' => $query->thisWeek(),
            'month' => $query->thisMonth(),
            default => $query,
        };
    }

    /**
     * Get statistik pengunjung
     */
    private function getStatistics($period)
    {
        $query = Visitor::query();

        if ($period !== 'all') {
            $query = $this->applyPeriodFilter($query, $period);
        }

        $totalVisitors = (clone $query)->count();
        $uniqueIPs = (clone $query)->select('ip_address')->distinct()->count('ip_address');
        $totalPages = (clone $query)->select('page_url')->distinct()->count('page_url');
        
        $topBrowser = (clone $query)
            ->select('browser', DB::raw('COUNT(*) as total'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderBy('total', 'desc')
            ->first();

        $topDevice = (clone $query)
            ->select('device', DB::raw('COUNT(*) as total'))
            ->whereNotNull('device')
            ->groupBy('device')
            ->orderBy('total', 'desc')
            ->first();

        $topPage = (clone $query)
            ->select('page_url', DB::raw('COUNT(*) as total'))
            ->whereNotNull('page_url')
            ->groupBy('page_url')
            ->orderBy('total', 'desc')
            ->first();

        return (object)[
            'total_visitors' => $totalVisitors,
            'unique_ips' => $uniqueIPs,
            'total_pages' => $totalPages,
            'top_browser' => $topBrowser,
            'top_device' => $topDevice,
            'top_page' => $topPage,
        ];
    }

    /**
     * Get data untuk chart
     */
    private function getChartData($period)
    {
        $query = Visitor::query();

        if ($period !== 'all') {
            $query = $this->applyPeriodFilter($query, $period);
        }

        // Data per jam untuk hari ini
        if ($period === 'today' || $period === 'all') {
            $hourlyData = (clone $query)
                ->selectRaw('HOUR(visited_at) as hour')
                ->selectRaw('COUNT(*) as total')
                ->today()
                ->groupBy('hour')
                ->orderBy('hour')
                ->pluck('total', 'hour');

            // Isi jam yang kosong dengan 0
            $labels = [];
            $data = [];
            for ($i = 0; $i < 24; $i++) {
                $labels[] = sprintf('%02d:00', $i);
                $data[] = $hourlyData->get($i, 0);
            }

            return (object)[
                'labels' => $labels,
                'data' => $data,
                'type' => 'hourly'
            ];
        }

        // Data per hari untuk minggu ini
        if ($period === 'week') {
            $dailyData = (clone $query)
                ->selectRaw('DATE(visited_at) as date')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $labels = $dailyData->pluck('date')->toArray();
            $data = $dailyData->pluck('total')->toArray();

            return (object)[
                'labels' => $labels,
                'data' => $data,
                'type' => 'daily'
            ];
        }

        // Data per tanggal untuk bulan ini
        if ($period === 'month') {
            $dailyData = (clone $query)
                ->selectRaw('DATE(visited_at) as date')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $labels = $dailyData->pluck('date')->toArray();
            $data = $dailyData->pluck('total')->toArray();

            return (object)[
                'labels' => $labels,
                'data' => $data,
                'type' => 'daily'
            ];
        }

        return (object)['labels' => [], 'data' => []];
    }

    /**
     * Export data visitor ke CSV
     */
    public function export(Request $request)
    {
        $period = $request->get('period', 'today');
        $query = Visitor::query();

        if ($period !== 'all') {
            $query = $this->applyPeriodFilter($query, $period);
        }

        $visitors = $query->orderBy('visited_at', 'desc')->get();

        $filename = 'visitors-' . $period . '-' . date('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($visitors) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'IP Address',
                'Browser',
                'OS',
                'Device',
                'Device Name',
                'Halaman',
                'Waktu Kunjungan'
            ]);

            foreach ($visitors as $visitor) {
                fputcsv($file, [
                    $visitor->ip_address,
                    $visitor->browser ?? '-',
                    $visitor->os ?? '-',
                    $visitor->device ?? '-',
                    $visitor->device_name ?? '-',
                    $visitor->page_url ?? '-',
                    $visitor->visited_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get data JSON untuk API
     */
    public function getStats(Request $request)
    {
        $period = $request->get('period', 'today');
        $stats = $this->getStatistics($period);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
