<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::query();

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

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowed = ['created_at', 'title', 'priority'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderByDesc('created_at');
        }

        return $query->paginate($request->input('per_page', 10));
    }

    public function show(Announcement $announcement)
    {
        return response()->json($announcement);
    }

    public function stats()
    {
        return response()->json([
            'total'  => Announcement::count(),
            'aktif'  => Announcement::where('status', 'Aktif')->count(),
            'urgent' => Announcement::where('priority', 'Urgent')->count(),
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

        $data = $request->only(['title', 'body', 'excerpt', 'priority', 'audience', 'location', 'status']);
        $data['created_by'] = $request->user()?->id;

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

        $data = $request->only(['title', 'body', 'excerpt', 'priority', 'audience', 'location', 'status']);

        if ($request->hasFile('image')) {
            if ($announcement->image_path) Storage::disk('public')->delete($announcement->image_path);
            $data['image_path'] = $request->file('image')->store('announcements', 'public');
        }
        if ($request->input('remove_image')) {
            if ($announcement->image_path) Storage::disk('public')->delete($announcement->image_path);
            $data['image_path'] = null;
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
