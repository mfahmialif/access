<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::with('unit:id,name');

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
                  ->orWhere('body', 'like', "%{$s}%")
                  ->orWhere('excerpt', 'like', "%{$s}%");
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sortBy = $request->input('sort_by', 'datetime');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowed = ['id', 'datetime', 'created_at', 'title', 'priority'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderByDesc('datetime');
        }

        return $query->paginate($request->input('per_page', 10));
    }

    public function show(Announcement $announcement)
    {
        return response()->json($announcement);
    }

    public function stats(Request $request)
    {
        $query = Announcement::with('unit:id,name');
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
            'total'  => (clone $query)->count(),
            'aktif'  => (clone $query)->where('status', 'Aktif')->count(),
            'urgent' => (clone $query)->where('priority', 'Urgent')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'priority' => 'required|in:Urgent,Normal,Info',
            'status'   => 'in:Aktif,Expired',
            'image'    => 'nullable|image|max:5120',
        ]);

        $data = $request->only(['title', 'body', 'excerpt', 'priority', 'audience', 'location', 'status', 'datetime']);
        $data['created_by'] = $request->user()?->id;
        if ($request->hasHeader('X-Unit-Id') && $request->header('X-Unit-Id') !== 'all') {
            $data['unit_id'] = $request->header('X-Unit-Id');
        } elseif ($request->filled('unit_id')) {
            $data['unit_id'] = $request->input('unit_id');
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('announcements', 'public');
        }

        return response()->json(Announcement::create($data), 201);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'priority' => 'required|in:Urgent,Normal,Info',
            'status'   => 'in:Aktif,Expired',
            'image'    => 'nullable|image|max:5120',
        ]);

        $data = $request->only(['title', 'body', 'excerpt', 'priority', 'audience', 'location', 'status', 'datetime']);

        if ($request->hasFile('image')) {
            if ($announcement->image_path) Storage::disk('public')->delete($announcement->image_path);
            $data['image_path'] = $request->file('image')->store('announcements', 'public');
        }
        if ($request->input('remove_image')) {
            if ($announcement->image_path) Storage::disk('public')->delete($announcement->image_path);
            $data['image_path'] = null;
        }

        if ($request->filled('unit_id')) {
            $data['unit_id'] = $request->input('unit_id');
        }
        
        $announcement->update($data);
        return response()->json($announcement);
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->image_path) Storage::disk('public')->delete($announcement->image_path);
        $announcement->delete();
        return response()->json(['message' => 'Pengumuman berhasil dihapus.']);
    }
}
