<?php

namespace App\Http\Controllers;

use App\Models\Monthly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MonthlyController extends Controller
{
    public function index(Request $request)
    {
        $query = Monthly::with(['creator:id,name', 'unit:id,name']);

        $unitId = $request->header('X-Unit-Id') ?? $request->query('unit_id');
        if ($unitId === 'null' || $unitId === 'undefined') {
            $unitId = null;
        }
        
        if ($unitId && $unitId !== 'all') {
            $query->where(function ($q) use ($unitId) {
                $q->where('unit_id', $unitId)->orWhereNull('unit_id');
            });
        }
        $query->orderBy('date')->orderBy('time');

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

        // Filter by month (YYYY-MM format)
        if ($request->filled('month')) {
            $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$request->month]);
        }

        $perPage = $request->input('per_page', 10);

        return $query->paginate($perPage);
    }

    public function show(Monthly $monthly)
    {
        return response()->json($monthly);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'date'     => 'required|date',
            'time'     => 'required',
            'category' => 'required|in:Artikel,Video,Gambar',
            'status'   => 'in:Aktif,Tidak Aktif',
            'image'    => 'nullable|image|max:5120',
            'video'    => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        $data = $request->only([
            'title', 'date', 'time', 'location', 'teacher', 'icon',
            'category', 'body', 'status',
        ]);
        $data['created_by'] = $request->user()->id;
        if ($request->hasHeader('X-Unit-Id') && $request->header('X-Unit-Id') !== 'all') {
            $data['unit_id'] = $request->header('X-Unit-Id');
        } elseif ($request->filled('unit_id')) {
            $data['unit_id'] = $request->input('unit_id');
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('monthlies', 'public');
        }

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('monthlies', 'public');
        }

        $monthly = Monthly::create($data);

        return response()->json($monthly, 201);
    }

    public function update(Request $request, Monthly $monthly)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'date'     => 'required|date',
            'time'     => 'required',
            'category' => 'required|in:Artikel,Video,Gambar',
            'status'   => 'in:Aktif,Tidak Aktif',
            'image'    => 'nullable|image|max:5120',
            'video'    => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        $data = $request->only(['title', 'date', 'time', 'location', 'teacher', 'icon', 'category', 'body', 'status']);

        if ($request->hasFile('image')) {
            if ($monthly->image_path) Storage::disk('public')->delete($monthly->image_path);
            $data['image_path'] = $request->file('image')->store('monthlies', 'public');
        }

        if ($request->hasFile('video')) {
            if ($monthly->video_path) Storage::disk('public')->delete($monthly->video_path);
            $data['video_path'] = $request->file('video')->store('monthlies', 'public');
        }

        if ($request->input('remove_image')) {
            if ($monthly->image_path) Storage::disk('public')->delete($monthly->image_path);
            $data['image_path'] = null;
        }

        if ($request->input('remove_video')) {
            if ($monthly->video_path) Storage::disk('public')->delete($monthly->video_path);
            $data['video_path'] = null;
        }

        if ($request->filled('unit_id')) {
            $data['unit_id'] = $request->input('unit_id');
        }
        
        $monthly->update($data);

        return response()->json($monthly);
    }

    public function destroy(Monthly $monthly)
    {
        if ($monthly->image_path) Storage::disk('public')->delete($monthly->image_path);
        if ($monthly->video_path) Storage::disk('public')->delete($monthly->video_path);

        $monthly->delete();

        return response()->json(['message' => 'Agenda bulanan berhasil dihapus.']);
    }
}
