<?php

namespace App\Http\Controllers;

use App\Events\ScreensaverUpdated;
use App\Models\Screensaver;
use App\Models\ScreensaverImage;
use App\Models\Setting;
use App\Models\TvDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScreensaverController extends Controller
{
    /**
     * Get the max video upload size (MB) from settings, default 100.
     */
    private function maxVideoSizeMb(): int
    {
        $setting = Setting::where('key', 'max_video_size_mb')->first();
        return $setting ? (int) $setting->value : 100;
    }

    /**
     * Determine media type from MIME.
     */
    private function detectMediaType($file): string
    {
        $mime = $file->getMimeType();
        return str_starts_with($mime, 'video/') ? 'video' : 'image';
    }

    /**
     * Build validation rules for media files.
     */
    private function mediaValidationRules(): array
    {
        $maxVideoKb = $this->maxVideoSizeMb() * 1024;
        $maxImageKb = 10240; // 10MB for images

        // Use the larger limit for the generic rule, then check per-file in controller
        $maxKb = max($maxVideoKb, $maxImageKb);

        return [
            'images'   => 'nullable|array',
            'images.*' => "file|mimes:jpg,jpeg,png,webp,gif,mp4,webm,mov|max:{$maxKb}",
        ];
    }

    public function stats()
    {
        $stats = Screensaver::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
        ")->first();

        $totalImages = ScreensaverImage::where('media_type', 'image')->count();
        $totalVideos = ScreensaverImage::where('media_type', 'video')->count();

        return response()->json([
            'total'        => (int) $stats->total,
            'active'       => (int) $stats->active,
            'inactive'     => (int) $stats->inactive,
            'total_images' => $totalImages,
            'total_videos' => $totalVideos,
            'total_media'  => $totalImages + $totalVideos,
        ]);
    }

    /**
     * List all screensavers (admin).
     */
    public function index(Request $request)
    {
        $query = Screensaver::with(['tvDevice', 'images']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('tvDevice', function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('location', 'like', "%{$s}%");
            });
        }

        if ($request->filled('tv_device_id')) {
            if ($request->tv_device_id === 'default') {
                $query->whereNull('tv_device_id');
            } else {
                $query->where('tv_device_id', $request->tv_device_id);
            }
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $query->orderByDesc('created_at');

        $perPage = $request->input('per_page', 10);

        return $query->paginate($perPage);
    }

    /**
     * Show a single screensaver (admin).
     */
    public function show(Screensaver $screensaver)
    {
        $screensaver->load(['tvDevice', 'images']);
        return response()->json($screensaver);
    }

    /**
     * Create a new screensaver + upload media (admin).
     */
    public function store(Request $request)
    {
        $rules = array_merge([
            'tv_device_id' => 'nullable|exists:tv_devices,id',
            'idle_timeout'  => 'required|integer|min:1|max:3600',
            'interval'      => 'required|integer|min:1|max:120',
            'is_active'     => 'boolean',
        ], $this->mediaValidationRules());

        $request->validate($rules);

        // Prevent duplicate: only one screensaver per TV target
        $exists = Screensaver::where('tv_device_id', $request->tv_device_id)->exists();
        if ($exists) {
            $label = $request->tv_device_id ? 'TV ini' : 'Default (Semua TV)';
            return response()->json([
                'message' => "Screensaver untuk {$label} sudah ada.",
                'errors'  => ['tv_device_id' => ["Screensaver untuk {$label} sudah ada. Silakan edit yang sudah ada."]],
            ], 422);
        }

        $screensaver = Screensaver::create([
            'tv_device_id' => $request->tv_device_id,
            'idle_timeout'  => $request->idle_timeout,
            'interval'      => $request->interval,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        // Upload media files
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $idx => $file) {
                $mediaType = $this->detectMediaType($file);
                $path = $file->store('screensavers', 'public');
                $screensaver->images()->create([
                    'image_path' => $path,
                    'media_type' => $mediaType,
                    'sort_order' => $idx,
                ]);
            }
        }

        $screensaver->load(['tvDevice', 'images']);

        event(new ScreensaverUpdated('created', $screensaver->id, $screensaver->tv_device_id));

        return response()->json($screensaver, 201);
    }

    /**
     * Update screensaver settings (admin).
     */
    public function update(Request $request, Screensaver $screensaver)
    {
        $rules = array_merge([
            'tv_device_id' => 'nullable|exists:tv_devices,id',
            'idle_timeout'  => 'required|integer|min:1|max:3600',
            'interval'      => 'required|integer|min:1|max:120',
            'is_active'     => 'boolean',
            'sort_orders'   => 'nullable|array',
        ], $this->mediaValidationRules());

        $request->validate($rules);

        // Prevent duplicate: only one screensaver per TV target (exclude self)
        $exists = Screensaver::where('tv_device_id', $request->tv_device_id)
            ->where('id', '!=', $screensaver->id)
            ->exists();
        if ($exists) {
            $label = $request->tv_device_id ? 'TV ini' : 'Default (Semua TV)';
            return response()->json([
                'message' => "Screensaver untuk {$label} sudah ada.",
                'errors'  => ['tv_device_id' => ["Screensaver untuk {$label} sudah ada. Silakan edit yang sudah ada."]],
            ], 422);
        }

        $screensaver->update([
            'tv_device_id' => $request->tv_device_id,
            'idle_timeout'  => $request->idle_timeout,
            'interval'      => $request->interval,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        // Update sort orders if provided
        if ($request->filled('sort_orders')) {
            foreach ($request->sort_orders as $imageId => $order) {
                ScreensaverImage::where('id', $imageId)
                    ->where('screensaver_id', $screensaver->id)
                    ->update(['sort_order' => $order]);
            }
        }

        // Upload new media files
        if ($request->hasFile('images')) {
            $maxSort = $screensaver->images()->max('sort_order') ?? -1;
            foreach ($request->file('images') as $idx => $file) {
                $mediaType = $this->detectMediaType($file);
                $path = $file->store('screensavers', 'public');
                $screensaver->images()->create([
                    'image_path' => $path,
                    'media_type' => $mediaType,
                    'sort_order' => $maxSort + $idx + 1,
                ]);
            }
        }

        $screensaver->load(['tvDevice', 'images']);

        event(new ScreensaverUpdated('updated', $screensaver->id, $screensaver->tv_device_id));

        return response()->json($screensaver);
    }

    /**
     * Delete screensaver + all its media (admin).
     */
    public function destroy(Screensaver $screensaver)
    {
        $tvDeviceId = $screensaver->tv_device_id;
        $screensaverId = $screensaver->id;

        // Delete media files from storage
        foreach ($screensaver->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $screensaver->delete();

        event(new ScreensaverUpdated('deleted', $screensaverId, $tvDeviceId));

        return response()->json(['message' => 'Screensaver berhasil dihapus.']);
    }

    /**
     * Add media to an existing screensaver (admin).
     */
    public function addImages(Request $request, Screensaver $screensaver)
    {
        $maxVideoKb = $this->maxVideoSizeMb() * 1024;
        $maxKb = max($maxVideoKb, 10240);

        $request->validate([
            'images'   => 'required|array|min:1',
            'images.*' => "file|mimes:jpg,jpeg,png,webp,gif,mp4,webm,mov|max:{$maxKb}",
        ]);

        $maxSort = $screensaver->images()->max('sort_order') ?? -1;

        $newImages = [];
        foreach ($request->file('images') as $idx => $file) {
            $mediaType = $this->detectMediaType($file);
            $path = $file->store('screensavers', 'public');
            $newImages[] = $screensaver->images()->create([
                'image_path' => $path,
                'media_type' => $mediaType,
                'sort_order' => $maxSort + $idx + 1,
            ]);
        }

        return response()->json($newImages, 201);
    }

    /**
     * Remove a single media from a screensaver (admin).
     */
    public function removeImage(Screensaver $screensaver, ScreensaverImage $image)
    {
        if ($image->screensaver_id !== $screensaver->id) {
            return response()->json(['message' => 'Media tidak ditemukan di screensaver ini.'], 404);
        }

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return response()->json(['message' => 'Media berhasil dihapus.']);
    }

    /**
     * Public route — get screensaver config for a TV device by token.
     */
    public function forTv(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json(['message' => 'Token required.'], 400);
        }

        $device = TvDevice::where('token', $token)->first();

        // Try device-specific screensaver first, then fall back to default (null tv_device_id)
        $screensaver = null;

        if ($device) {
            $screensaver = Screensaver::with(['images', 'unit:id,name'])
                ->where('tv_device_id', $device->id)
                ->where('is_active', true)
                ->first();
        }

        // Fallback to default screensaver
        if (!$screensaver) {
            $screensaver = Screensaver::with(['images', 'unit:id,name'])
                ->whereNull('tv_device_id')
                ->where('is_active', true)
                ->first();
        }

        if (!$screensaver || $screensaver->images->isEmpty()) {
            return response()->json(['active' => false]);
        }

        return response()->json([
            'active'       => true,
            'idle_timeout' => $screensaver->idle_timeout,
            'interval'     => $screensaver->interval,
            'images'       => $screensaver->images->map(fn ($img) => [
                'id'         => $img->id,
                'url'        => asset('storage/' . $img->image_path),
                'media_type' => $img->media_type ?? 'image',
                'sort_order' => $img->sort_order,
            ])->values(),
        ]);
    }
}
