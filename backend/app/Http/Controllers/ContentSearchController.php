<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContentSearchController extends Controller
{
    /**
     * Unified content search for push konten dropdown.
     * Single endpoint instead of 7 parallel requests.
     */
    public function search(Request $request)
    {
        $search = $request->input('q', '');
        $limit = (int) $request->input('limit', 5);

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $like = "%{$search}%";

        $unitId = $request->header('X-Unit-Id') ?? $request->query('unit_id');
        if ($unitId === 'null' || $unitId === 'undefined') {
            $unitId = null;
        }
        $applyUnit = function($q) use ($unitId) {
            if ($unitId && $unitId !== 'all') {
                $q->where(function ($query) use ($unitId) {
                    $query->where('unit_id', $unitId)->orWhereNull('unit_id');
                });
            }
        };

        $results = collect();

        // News (Info Terkini)
        $newsQuery = DB::table('news')
            ->where('title', 'like', $like);
        $applyUnit($newsQuery);
        $news = $newsQuery->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "📰 {$r->title}",
                'path' => "/info-terkini/{$r->id}",
            ]);
        $results = $results->merge($news);

        // Agendas (Agenda Harian)
        $agendasQuery = DB::table('agendas')
            ->where('title', 'like', $like);
        $applyUnit($agendasQuery);
        $agendas = $agendasQuery->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "📅 {$r->title}",
                'path' => "/agenda-harian/{$r->id}",
            ]);
        $results = $results->merge($agendas);

        // Weeklies (Agenda Mingguan)
        $weekliesQuery = DB::table('weeklies')
            ->where('title', 'like', $like);
        $applyUnit($weekliesQuery);
        $weeklies = $weekliesQuery->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "📆 {$r->title}",
                'path' => "/agenda-mingguan/{$r->id}",
            ]);
        $results = $results->merge($weeklies);

        // Monthlies (Agenda Bulanan)
        $monthliesQuery = DB::table('monthlies')
            ->where('title', 'like', $like);
        $applyUnit($monthliesQuery);
        $monthlies = $monthliesQuery->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "🗓️ {$r->title}",
                'path' => "/agenda-bulanan/{$r->id}",
            ]);
        $results = $results->merge($monthlies);

        // Galleries (Gallery & Video)
        $galleriesQuery = DB::table('galleries')
            ->where('title', 'like', $like);
        $applyUnit($galleriesQuery);
        $galleries = $galleriesQuery->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "🎬 {$r->title}",
                'path' => "/gallery-video/{$r->id}",
            ]);
        $results = $results->merge($galleries);

        // Announcements (Pengumuman)
        $announcementsQuery = DB::table('announcements')
            ->where('title', 'like', $like);
        $applyUnit($announcementsQuery);
        $announcements = $announcementsQuery->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "📢 {$r->title}",
                'path' => "/pengumuman/{$r->id}",
            ]);
        $results = $results->merge($announcements);

        // App Links (Apps Portal)
        $appLinksQuery = DB::table('app_links')
            ->where('status', 'Published')
            ->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                  ->orWhere('subtitle', 'like', $like);
            });
        $applyUnit($appLinksQuery);
        $appLinks = $appLinksQuery->orderBy('sort_order')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "📱 {$r->title}",
                'path' => "/apps?open={$r->id}",
            ]);
        $results = $results->merge($appLinks);

        return response()->json($results->values());
    }
}
