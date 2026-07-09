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
        $applyUnit = function($q, $table) use ($unitId) {
            if ($unitId && $unitId !== 'all') {
                $q->where(function ($query) use ($unitId, $table) {
                    $query->where("{$table}.unit_id", $unitId)->orWhereNull("{$table}.unit_id");
                });
            }
        };

        $results = collect();

        // News (Info Terkini)
        $newsQuery = DB::table('news')
            ->leftJoin('units', 'news.unit_id', '=', 'units.id')
            ->where('news.title', 'like', $like);
        $applyUnit($newsQuery, 'news');
        $news = $newsQuery->orderByDesc('news.id')
            ->limit($limit)
            ->get(['news.id', 'news.title', 'units.name as unit_name'])
            ->map(fn ($r) => [
                'name' => "📰 {$r->title}" . ($r->unit_name ? " ({$r->unit_name})" : ""),
                'path' => "/info-terkini/{$r->id}",
            ]);
        $results = $results->merge($news);

        // Agendas (Agenda Harian)
        $agendasQuery = DB::table('agendas')
            ->leftJoin('units', 'agendas.unit_id', '=', 'units.id')
            ->where('agendas.title', 'like', $like);
        $applyUnit($agendasQuery, 'agendas');
        $agendas = $agendasQuery->orderByDesc('agendas.id')
            ->limit($limit)
            ->get(['agendas.id', 'agendas.title', 'units.name as unit_name'])
            ->map(fn ($r) => [
                'name' => "📅 {$r->title}" . ($r->unit_name ? " ({$r->unit_name})" : ""),
                'path' => "/agenda-harian/{$r->id}",
            ]);
        $results = $results->merge($agendas);

        // Weeklies (Agenda Mingguan)
        $weekliesQuery = DB::table('weeklies')
            ->leftJoin('units', 'weeklies.unit_id', '=', 'units.id')
            ->where('weeklies.title', 'like', $like);
        $applyUnit($weekliesQuery, 'weeklies');
        $weeklies = $weekliesQuery->orderByDesc('weeklies.id')
            ->limit($limit)
            ->get(['weeklies.id', 'weeklies.title', 'units.name as unit_name'])
            ->map(fn ($r) => [
                'name' => "📆 {$r->title}" . ($r->unit_name ? " ({$r->unit_name})" : ""),
                'path' => "/agenda-mingguan/{$r->id}",
            ]);
        $results = $results->merge($weeklies);

        // Monthlies (Agenda Bulanan)
        $monthliesQuery = DB::table('monthlies')
            ->leftJoin('units', 'monthlies.unit_id', '=', 'units.id')
            ->where('monthlies.title', 'like', $like);
        $applyUnit($monthliesQuery, 'monthlies');
        $monthlies = $monthliesQuery->orderByDesc('monthlies.id')
            ->limit($limit)
            ->get(['monthlies.id', 'monthlies.title', 'units.name as unit_name'])
            ->map(fn ($r) => [
                'name' => "🗓️ {$r->title}" . ($r->unit_name ? " ({$r->unit_name})" : ""),
                'path' => "/agenda-bulanan/{$r->id}",
            ]);
        $results = $results->merge($monthlies);

        // Galleries (Gallery & Video)
        $galleriesQuery = DB::table('galleries')
            ->leftJoin('units', 'galleries.unit_id', '=', 'units.id')
            ->where('galleries.title', 'like', $like);
        $applyUnit($galleriesQuery, 'galleries');
        $galleries = $galleriesQuery->orderByDesc('galleries.id')
            ->limit($limit)
            ->get(['galleries.id', 'galleries.title', 'units.name as unit_name'])
            ->map(fn ($r) => [
                'name' => "🎬 {$r->title}" . ($r->unit_name ? " ({$r->unit_name})" : ""),
                'path' => "/gallery-video/{$r->id}",
            ]);
        $results = $results->merge($galleries);

        // Announcements (Pengumuman)
        $announcementsQuery = DB::table('announcements')
            ->leftJoin('units', 'announcements.unit_id', '=', 'units.id')
            ->where('announcements.title', 'like', $like);
        $applyUnit($announcementsQuery, 'announcements');
        $announcements = $announcementsQuery->orderByDesc('announcements.id')
            ->limit($limit)
            ->get(['announcements.id', 'announcements.title', 'units.name as unit_name'])
            ->map(fn ($r) => [
                'name' => "📢 {$r->title}" . ($r->unit_name ? " ({$r->unit_name})" : ""),
                'path' => "/pengumuman/{$r->id}",
            ]);
        $results = $results->merge($announcements);

        // App Links (Apps Portal)
        $appLinksQuery = DB::table('app_links')
            ->leftJoin('units', 'app_links.unit_id', '=', 'units.id')
            ->where('app_links.status', 'Published')
            ->where(function ($q) use ($like) {
                $q->where('app_links.title', 'like', $like)
                  ->orWhere('app_links.subtitle', 'like', $like);
            });
        $applyUnit($appLinksQuery, 'app_links');
        $appLinks = $appLinksQuery->orderBy('app_links.sort_order')
            ->limit($limit)
            ->get(['app_links.id', 'app_links.title', 'units.name as unit_name'])
            ->map(fn ($r) => [
                'name' => "📱 {$r->title}" . ($r->unit_name ? " ({$r->unit_name})" : ""),
                'path' => "/apps?open={$r->id}",
            ]);
        $results = $results->merge($appLinks);

        return response()->json($results->values());
    }
}
