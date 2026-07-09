<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Gallery;
use App\Models\Monthly;
use App\Models\News;
use App\Models\TvCommandLog;
use App\Models\TvDevice;
use App\Models\User;
use App\Models\Weekly;

class DashboardController extends Controller
{
    /**
     * GET /dashboard/stats — Real aggregated stats for the admin dashboard.
     */
    public function stats()
    {
        $this->refreshStatuses();

        $unitId = request()->header('X-Unit-Id');

        $applyUnit = function($query) use ($unitId) {
            if ($unitId && $unitId !== 'all') {
                $query->where('unit_id', $unitId);
            }
        };

        // ── TV devices ──
        $tvQuery = TvDevice::query();
        $applyUnit($tvQuery);
        $totalTvs   = (clone $tvQuery)->count();
        $onlineTvs  = (clone $tvQuery)->where('status', 'online')->count();
        $offlineTvs = (clone $tvQuery)->where('status', 'offline')->count();

        // ── Content (all publishable models) ──
        $contentModels = [Agenda::class, Weekly::class, Monthly::class, Gallery::class, News::class, Announcement::class];
        $totalContent = 0;
        $newContent30d = 0;
        foreach ($contentModels as $model) {
            $query = $model::query();
            $applyUnit($query);
            $totalContent  += (clone $query)->count();
            $newContent30d += (clone $query)->where('created_at', '>=', now()->subDays(30))->count();
        }

        // ── Users ──
        $totalUsers = User::count();

        // ── Trends: growth over the last 30 days (new items vs. older baseline) ──
        $trend = function (int $total, int $new30d): int {
            $baseline = $total - $new30d;
            if ($baseline <= 0) return $new30d > 0 ? 100 : 0;
            return (int) round(($new30d / $baseline) * 100);
        };

        $newTvsQuery = TvDevice::where('created_at', '>=', now()->subDays(30));
        $applyUnit($newTvsQuery);
        $newTvs30d   = (clone $newTvsQuery)->count();
        $newUsers30d = User::where('created_at', '>=', now()->subDays(30))->count();

        // ── Recent activity (command logs) ──
        $activities = TvCommandLog::with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(fn ($log) => [
                'time'   => $log->created_at->format('H:i'),
                'date'   => $log->created_at->translatedFormat('d M Y'),
                'user'   => $log->user?->name ?? 'System',
                'action' => $this->describeCommand($log),
                'status' => $log->status,
            ]);

        // ── Network uptime: % of online devices among registered (non-setup) devices ──
        $registered = $onlineTvs + $offlineTvs;
        $uptime = $registered > 0 ? round(($onlineTvs / $registered) * 100, 1) : 0;

        return response()->json([
            'stats' => [
                'total_tvs' => [
                    'value' => $totalTvs,
                    'trend' => $trend($totalTvs, $newTvs30d),
                ],
                'active_tvs' => [
                    'value' => $onlineTvs,
                    'trend' => $totalTvs > 0 ? (int) round(($onlineTvs / $totalTvs) * 100) : 0,
                ],
                'total_content' => [
                    'value' => $totalContent,
                    'trend' => $trend($totalContent, $newContent30d),
                ],
                'users' => [
                    'value' => $totalUsers,
                    'trend' => $trend($totalUsers, $newUsers30d),
                ],
            ],
            'activities' => $activities,
            'network' => [
                'uptime'  => $uptime,
                'online'  => $onlineTvs,
                'offline' => $offlineTvs,
            ],
        ]);
    }

    /**
     * Human-readable label for a command log entry.
     */
    private function describeCommand(TvCommandLog $log): string
    {
        $labels = [
            'navigate' => 'Push konten',
            'reload'   => 'Reload',
            'home'     => 'Kembali ke Home',
            'banner'   => 'Tampilkan banner',
        ];

        $label = $labels[$log->command] ?? ucfirst($log->command);

        return "{$label} → {$log->target}";
    }

    /**
     * Auto-mark devices offline when heartbeat is stale (same rule as TvDeviceController).
     */
    private function refreshStatuses(): void
    {
        TvDevice::where('status', 'online')
            ->where('last_heartbeat', '<', now()->subMinutes(3))
            ->update(['status' => 'offline']);
    }
}
