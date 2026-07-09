<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = Gallery::with('unit:id,name');

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
                  ->orWhere('description', 'like', "%{$s}%");
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

        $perPage = $request->input('per_page', 12);

        return $query->paginate($perPage);
    }

    public function show(Gallery $gallery)
    {
        return response()->json($gallery);
    }

    public function stats(Request $request)
    {
        $query = Gallery::with('unit:id,name');
        $unitId = $request->header('X-Unit-Id') ?? $request->query('unit_id');
        if ($unitId === 'null' || $unitId === 'undefined') {
            $unitId = null;
        }
        
        if ($unitId && $unitId !== 'all') {
            $query->where(function ($q) use ($unitId) {
                $q->where('unit_id', $unitId)->orWhereNull('unit_id');
            });
        }

        return response()->json([
            'total' => (clone $query)->count(),
            'gambar' => (clone $query)->where('category', 'Gambar')->count(),
            'video' => (clone $query)->where('category', 'Video')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|in:Gambar,Video',
            'description' => 'nullable|string',
            'status'      => 'in:Published,Draft',
            'image'       => 'nullable|image|max:5120',
            'video'       => 'nullable|mimes:mp4,webm,ogg|max:51200',
            'duration'    => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'category', 'description', 'status', 'duration', 'datetime']);
        $data['created_by'] = $request->user()?->id;
        if ($request->hasHeader('X-Unit-Id') && $request->header('X-Unit-Id') !== 'all') {
            $data['unit_id'] = $request->header('X-Unit-Id');
        } elseif ($request->filled('unit_id')) {
            $data['unit_id'] = $request->input('unit_id');
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('galleries', 'public');
        }

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('galleries', 'public');
        }

        $gallery = Gallery::create($data);

        return response()->json($gallery, 201);
    }

    public function update(Request $request, Gallery $gallery)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|in:Gambar,Video',
            'description' => 'nullable|string',
            'status'      => 'in:Published,Draft',
            'image'       => 'nullable|image|max:5120',
            'video'       => 'nullable|mimes:mp4,webm,ogg|max:51200',
            'duration'    => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'category', 'description', 'status', 'duration', 'datetime']);

        if ($request->hasFile('image')) {
            if ($gallery->image_path) Storage::disk('public')->delete($gallery->image_path);
            $data['image_path'] = $request->file('image')->store('galleries', 'public');
        }

        if ($request->hasFile('video')) {
            if ($gallery->video_path) Storage::disk('public')->delete($gallery->video_path);
            $data['video_path'] = $request->file('video')->store('galleries', 'public');
        }

        if ($request->input('remove_image')) {
            if ($gallery->image_path) Storage::disk('public')->delete($gallery->image_path);
            $data['image_path'] = null;
        }

        if ($request->input('remove_video')) {
            if ($gallery->video_path) Storage::disk('public')->delete($gallery->video_path);
            $data['video_path'] = null;
        }

        if ($request->filled('unit_id')) {
            $data['unit_id'] = $request->input('unit_id');
        }
        
        $gallery->update($data);

        return response()->json($gallery);
    }

    public function destroy(Gallery $gallery)
    {
        if ($gallery->image_path) Storage::disk('public')->delete($gallery->image_path);
        if ($gallery->video_path) Storage::disk('public')->delete($gallery->video_path);

        $gallery->delete();

        return response()->json(['message' => 'Media berhasil dihapus.']);
    }
}
