<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgendaController extends Controller
{
    /**
     * Daftar semua agenda.
     */
    public function index(Request $request)
    {
        $query = Agenda::with(['creator:id,name', 'unit:id,name']);

        $unitId = $request->header('X-Unit-Id') ?? $request->query('unit_id');
        if ($unitId === 'null' || $unitId === 'undefined') {
            $unitId = null;
        }
        
        if ($unitId && $unitId !== 'all') {
            $query->where(function ($q) use ($unitId) {
                $q->where('unit_id', $unitId)->orWhereNull('unit_id');
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('teacher', 'like', "%{$search}%");
            });
        }

        // Filter kategori
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 10);
        $agendas = $query->orderBy('time')->paginate($perPage);

        return response()->json($agendas);
    }

    /**
     * Buat agenda baru (dengan file upload).
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'time'        => 'required|date_format:H:i',
            'location'    => 'nullable|string|max:255',
            'teacher'     => 'nullable|string|max:255',
            'icon'        => 'nullable|string|max:100',
            'category'    => 'required|in:Artikel,Video,Gambar',
            'body'        => 'nullable|string',
            'status'      => 'in:Aktif,Mendatang,Selesai',
            'image'       => 'nullable|image|max:5120',   // max 5MB
            'video'       => 'nullable|mimes:mp4,webm,ogg|max:51200', // max 50MB
        ]);

        $data = $request->only([
            'title', 'time', 'location', 'teacher', 'icon',
            'category', 'body', 'status',
        ]);
        $data['created_by'] = $request->user()->id;
        if ($request->hasHeader('X-Unit-Id') && $request->header('X-Unit-Id') !== 'all') {
            $data['unit_id'] = $request->header('X-Unit-Id');
        } elseif ($request->filled('unit_id')) {
            $data['unit_id'] = $request->input('unit_id');
        }

        // Upload image (untuk Gambar & Artikel banner)
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('agendas/images', 'public');
        }

        // Upload video (untuk Video)
        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('agendas/videos', 'public');
        }

        $agenda = Agenda::create($data);
        $agenda->load('creator:id,name');

        return response()->json($agenda, 201);
    }

    /**
     * Detail satu agenda.
     */
    public function show(Agenda $agenda)
    {
        $agenda->load('creator:id,name');
        return response()->json($agenda);
    }

    /**
     * Update agenda (dengan file upload).
     */
    public function update(Request $request, Agenda $agenda)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'time'        => 'required|date_format:H:i',
            'location'    => 'nullable|string|max:255',
            'teacher'     => 'nullable|string|max:255',
            'icon'        => 'nullable|string|max:100',
            'category'    => 'required|in:Artikel,Video,Gambar',
            'body'        => 'nullable|string',
            'status'      => 'in:Aktif,Tidak Aktif',
            'image'       => 'nullable|image|max:5120',
            'video'       => 'nullable|mimes:mp4,webm,ogg|max:51200',
        ]);

        $data = $request->only([
            'title', 'time', 'location', 'teacher', 'icon',
            'category', 'body', 'status',
        ]);

        // Upload image baru — hapus lama
        if ($request->hasFile('image')) {
            if ($agenda->image_path) {
                Storage::disk('public')->delete($agenda->image_path);
            }
            $data['image_path'] = $request->file('image')->store('agendas/images', 'public');
        }

        // Hapus gambar jika diminta
        if ($request->boolean('remove_image') && $agenda->image_path) {
            Storage::disk('public')->delete($agenda->image_path);
            $data['image_path'] = null;
        }

        // Upload video baru — hapus lama
        if ($request->hasFile('video')) {
            if ($agenda->video_path) {
                Storage::disk('public')->delete($agenda->video_path);
            }
            $data['video_path'] = $request->file('video')->store('agendas/videos', 'public');
        }

        // Hapus video jika diminta
        if ($request->boolean('remove_video') && $agenda->video_path) {
            Storage::disk('public')->delete($agenda->video_path);
            $data['video_path'] = null;
        }

        if ($request->filled('unit_id')) {
            $data['unit_id'] = $request->input('unit_id');
        }
        
        $agenda->update($data);
        $agenda->load('creator:id,name');

        return response()->json($agenda);
    }

    /**
     * Hapus agenda.
     */
    public function destroy(Agenda $agenda)
    {
        // Hapus file terkait
        if ($agenda->image_path) {
            Storage::disk('public')->delete($agenda->image_path);
        }
        if ($agenda->video_path) {
            Storage::disk('public')->delete($agenda->video_path);
        }

        $agenda->delete();

        return response()->json(['message' => 'Agenda berhasil dihapus.']);
    }

    /**
     * Upload file dari editor (Quill) — gambar/video.
     * Mengembalikan URL public untuk di-embed di body HTML.
     */
    public function uploadEditorFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // max 50MB
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType();

        if (str_starts_with($mime, 'image/')) {
            $path = $file->store('agendas/editor', 'public');
        } elseif (str_starts_with($mime, 'video/')) {
            $path = $file->store('agendas/editor', 'public');
        } else {
            return response()->json(['message' => 'Tipe file tidak didukung.'], 422);
        }

        return response()->json([
            'url' => '/storage/' . $path,
        ]);
    }

    /**
     * Hapus file editor berdasarkan URL.
     */
    public function deleteEditorFile(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        $url = $request->input('url');

        // Ambil path relatif dari URL (/storage/agendas/editor/xxx.jpg -> agendas/editor/xxx.jpg)
        $path = str_replace('/storage/', '', $url);

        // Hanya izinkan hapus file di folder agendas/editor
        if (!str_starts_with($path, 'agendas/editor/')) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return response()->json(['message' => 'File berhasil dihapus.']);
        }

        return response()->json(['message' => 'File tidak ditemukan.'], 404);
    }
}
