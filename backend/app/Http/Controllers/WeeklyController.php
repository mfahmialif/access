<?php

namespace App\Http\Controllers;

use App\Models\Weekly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WeeklyController extends Controller
{
    /**
     * Display a listing of weeklies.
     */
    public function index(Request $request)
    {
        $query = Weekly::with('unit:id,name')->orderBy('day')->orderBy('time');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('location', 'like', "%{$s}%")
                  ->orWhere('teacher', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('day')) {
            $query->where('day', $request->day);
        }

        $perPage = $request->input('per_page', 10);

        return $query->paginate($perPage);
    }

    /**
     * Display the specified weekly.
     */
    public function show(Weekly $weekly)
    {
        return response()->json($weekly);
    }

    /**
     * Store a newly created weekly.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'day'      => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'time'     => 'required',
            'category' => 'required|in:Artikel,Video,Gambar',
            'status'   => 'in:Aktif,Tidak Aktif',
            'image'    => 'nullable|image|max:5120',
            'video'    => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        $data = $request->only([
            'title', 'day', 'time', 'location', 'teacher', 'icon',
            'category', 'body', 'status',
        ]);
        $data['created_by'] = $request->user()->id;
        if ($request->hasHeader('X-Unit-Id') && $request->header('X-Unit-Id') !== 'all') {
            $data['unit_id'] = $request->header('X-Unit-Id');
        } elseif ($request->filled('unit_id')) {
            $data['unit_id'] = $request->input('unit_id');
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('weeklies', 'public');
        }

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('weeklies', 'public');
        }

        $weekly = Weekly::create($data);

        return response()->json($weekly, 201);
    }

    /**
     * Update the specified weekly.
     */
    public function update(Request $request, Weekly $weekly)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'day'      => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'time'     => 'required',
            'category' => 'required|in:Artikel,Video,Gambar',
            'status'   => 'in:Aktif,Tidak Aktif',
            'image'    => 'nullable|image|max:5120',
            'video'    => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        $data = $request->only(['title', 'day', 'time', 'location', 'teacher', 'icon', 'category', 'body', 'status']);

        if ($request->hasFile('image')) {
            if ($weekly->image_path) {
                Storage::disk('public')->delete($weekly->image_path);
            }
            $data['image_path'] = $request->file('image')->store('weeklies', 'public');
        }

        if ($request->hasFile('video')) {
            if ($weekly->video_path) {
                Storage::disk('public')->delete($weekly->video_path);
            }
            $data['video_path'] = $request->file('video')->store('weeklies', 'public');
        }

        if ($request->input('remove_image')) {
            if ($weekly->image_path) {
                Storage::disk('public')->delete($weekly->image_path);
            }
            $data['image_path'] = null;
        }

        if ($request->input('remove_video')) {
            if ($weekly->video_path) {
                Storage::disk('public')->delete($weekly->video_path);
            }
            $data['video_path'] = null;
        }

        if ($request->filled('unit_id')) {
            $data['unit_id'] = $request->input('unit_id');
        }
        
        $weekly->update($data);

        return response()->json($weekly);
    }

    /**
     * Remove the specified weekly.
     */
    public function destroy(Weekly $weekly)
    {
        if ($weekly->image_path) {
            Storage::disk('public')->delete($weekly->image_path);
        }
        if ($weekly->video_path) {
            Storage::disk('public')->delete($weekly->video_path);
        }

        $weekly->delete();

        return response()->json(['message' => 'Agenda mingguan berhasil dihapus.']);
    }
}
