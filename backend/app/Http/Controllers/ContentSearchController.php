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

        $results = collect();

        // News (Info Terkini)
        $news = DB::table('news')
            ->where('title', 'like', $like)
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "📰 {$r->title}",
                'path' => "/info-terkini/{$r->id}",
            ]);
        $results = $results->merge($news);

        // Agendas (Agenda Harian)
        $agendas = DB::table('agendas')
            ->where('title', 'like', $like)
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "📅 {$r->title}",
                'path' => "/agenda-harian/{$r->id}",
            ]);
        $results = $results->merge($agendas);

        // Weeklies (Agenda Mingguan)
        $weeklies = DB::table('weeklies')
            ->where('title', 'like', $like)
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "📆 {$r->title}",
                'path' => "/agenda-mingguan/{$r->id}",
            ]);
        $results = $results->merge($weeklies);

        // Monthlies (Agenda Bulanan)
        $monthlies = DB::table('monthlies')
            ->where('title', 'like', $like)
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "🗓️ {$r->title}",
                'path' => "/agenda-bulanan/{$r->id}",
            ]);
        $results = $results->merge($monthlies);

        // Galleries (Gallery & Video)
        $galleries = DB::table('galleries')
            ->where('title', 'like', $like)
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "🎬 {$r->title}",
                'path' => "/gallery-video/{$r->id}",
            ]);
        $results = $results->merge($galleries);

        // Announcements (Pengumuman)
        $announcements = DB::table('announcements')
            ->where('title', 'like', $like)
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(fn ($r) => [
                'name' => "📢 {$r->title}",
                'path' => "/pengumuman/{$r->id}",
            ]);
        $results = $results->merge($announcements);

        // App Links (Apps Portal)
        $appLinks = DB::table('app_links')
            ->where('status', 'Published')
            ->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                  ->orWhere('subtitle', 'like', $like);
            })
            ->orderBy('sort_order')
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
