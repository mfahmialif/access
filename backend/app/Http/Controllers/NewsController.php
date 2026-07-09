<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    public function stats(Request $request)
    {
        $query = DB::table('news');
        $unitId = $request->header('X-Unit-Id') ?? $request->query('unit_id');
        if ($unitId === 'null' || $unitId === 'undefined') {
            $unitId = null;
        }
        
        if ($unitId && $unitId !== 'all') {
            $query->where(function ($q) use ($unitId) {
                $q->where('unit_id', $unitId)->orWhereNull('unit_id');
            });
        }

        $stats = $query->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN category = 'Artikel' THEN 1 ELSE 0 END) as artikel,
            SUM(CASE WHEN category = 'Video' THEN 1 ELSE 0 END) as video,
            SUM(CASE WHEN category = 'Gambar' THEN 1 ELSE 0 END) as gambar
        ")->first();

        return response()->json($stats);
    }

    public function index(Request $request)
    {
        $query = News::with('unit:id,name');

        $unitId = $request->header('X-Unit-Id') ?? $request->query('unit_id');
        if ($unitId === 'null' || $unitId === 'undefined') {
            $unitId = null;
        }
        
        if ($unitId && $unitId !== 'all') {
            $query->where(function ($q) use ($unitId) {
                $q->where('unit_id', $unitId)->orWhereNull('unit_id');
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('body', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'datetime');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSort = ['id', 'datetime', 'created_at', 'title', 'category'];
        if (in_array($sortBy, $allowedSort)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderByDesc('datetime');
        }

        $perPage = $request->input('per_page', 6);

        return $query->paginate($perPage);
    }

    public function show(News $news)
    {
        return response()->json($news);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'category' => 'required|in:Artikel,Video,Gambar',
            'status'   => 'in:Published,Draft',
            'image'    => 'nullable|image|max:5120',
            'video'    => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        $data = $request->only(['title', 'category', 'body', 'speaker', 'duration', 'status', 'datetime']);
        $data['created_by'] = $request->user()?->id;
        if ($request->hasHeader('X-Unit-Id') && $request->header('X-Unit-Id') !== 'all') {
            $data['unit_id'] = $request->header('X-Unit-Id');
        } elseif ($request->filled('unit_id')) {
            $data['unit_id'] = $request->input('unit_id');
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('news', 'public');
        }
        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('news', 'public');
        }

        return response()->json(News::create($data), 201);
    }

    public function update(Request $request, News $news)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'category' => 'required|in:Artikel,Video,Gambar',
            'status'   => 'in:Published,Draft',
            'image'    => 'nullable|image|max:5120',
            'video'    => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        $data = $request->only(['title', 'category', 'body', 'speaker', 'duration', 'status', 'datetime']);

        if ($request->hasFile('image')) {
            if ($news->image_path) Storage::disk('public')->delete($news->image_path);
            $data['image_path'] = $request->file('image')->store('news', 'public');
        }
        if ($request->hasFile('video')) {
            if ($news->video_path) Storage::disk('public')->delete($news->video_path);
            $data['video_path'] = $request->file('video')->store('news', 'public');
        }
        if ($request->input('remove_image')) {
            if ($news->image_path) Storage::disk('public')->delete($news->image_path);
            $data['image_path'] = null;
        }
        if ($request->input('remove_video')) {
            if ($news->video_path) Storage::disk('public')->delete($news->video_path);
            $data['video_path'] = null;
        }

        if ($request->filled('unit_id')) {
            $data['unit_id'] = $request->input('unit_id');
        }
        
        $news->update($data);
        return response()->json($news);
    }

    public function destroy(News $news)
    {
        if ($news->image_path) Storage::disk('public')->delete($news->image_path);
        if ($news->video_path) Storage::disk('public')->delete($news->video_path);
        $news->delete();
        return response()->json(['message' => 'Konten berhasil dihapus.']);
    }
}
