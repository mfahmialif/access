<?php

namespace App\Http\Controllers;

use App\Events\ScreensaverUpdated;
use App\Models\Screensaver;
use App\Models\ScreensaverImage;
use App\Models\TvDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScreensaverController extends Controller
{
    public function stats()
    {
        $stats = Screensaver::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
        ")->first();

        $totalImages = ScreensaverImage::count();

        return response()->json([
            'total'        => (int) $stats->total,
            'active'       => (int) $stats->active,
            'inactive'     => (int) $stats->inactive,
            'total_images' => $totalImages,
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
     * Create a new screensaver + upload images (admin).
     */
    public function store(Request $request)
    {
        $request->validate([
            'tv_device_id' => 'nullable|exists:tv_devices,id',
            'idle_timeout'  => 'required|integer|min:1|max:3600',
            'interval'      => 'required|integer|min:1|max:120',
            'is_active'     => 'boolean',
            'images'        => 'nullable|array',
            'images.*'      => 'image|max:10240',
        ]);

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

        // Upload images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $idx => $file) {
                $path = $file->store('screensavers', 'public');
                $screensaver->images()->create([
                    'image_path' => $path,
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
        $request->validate([
            'tv_device_id' => 'nullable|exists:tv_devices,id',
            'idle_timeout'  => 'required|integer|min:1|max:3600',
            'interval'      => 'required|integer|min:1|max:120',
            'is_active'     => 'boolean',
            'images'        => 'nullable|array',
            'images.*'      => 'image|max:10240',
            'sort_orders'   => 'nullable|array',
        ]);

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

        // Upload new images
        if ($request->hasFile('images')) {
            $maxSort = $screensaver->images()->max('sort_order') ?? -1;
            foreach ($request->file('images') as $idx => $file) {
                $path = $file->store('screensavers', 'public');
                $screensaver->images()->create([
                    'image_path' => $path,
                    'sort_order' => $maxSort + $idx + 1,
                ]);
            }
        }

        $screensaver->load(['tvDevice', 'images']);

        event(new ScreensaverUpdated('updated', $screensaver->id, $screensaver->tv_device_id));

        return response()->json($screensaver);
    }

    /**
     * Delete screensaver + all its images (admin).
     */
    public function destroy(Screensaver $screensaver)
    {
        $tvDeviceId = $screensaver->tv_device_id;
        $screensaverId = $screensaver->id;

        // Delete image files from storage
        foreach ($screensaver->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $screensaver->delete();

        event(new ScreensaverUpdated('deleted', $screensaverId, $tvDeviceId));

        return response()->json(['message' => 'Screensaver berhasil dihapus.']);
    }

    /**
     * Add images to an existing screensaver (admin).
     */
    public function addImages(Request $request, Screensaver $screensaver)
    {
        $request->validate([
            'images'   => 'required|array|min:1',
            'images.*' => 'image|max:10240',
        ]);

        $maxSort = $screensaver->images()->max('sort_order') ?? -1;

        $newImages = [];
        foreach ($request->file('images') as $idx => $file) {
            $path = $file->store('screensavers', 'public');
            $newImages[] = $screensaver->images()->create([
                'image_path' => $path,
                'sort_order' => $maxSort + $idx + 1,
            ]);
        }

        return response()->json($newImages, 201);
    }

    /**
     * Remove a single image from a screensaver (admin).
     */
    public function removeImage(Screensaver $screensaver, ScreensaverImage $image)
    {
        if ($image->screensaver_id !== $screensaver->id) {
            return response()->json(['message' => 'Gambar tidak ditemukan di screensaver ini.'], 404);
        }

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return response()->json(['message' => 'Gambar berhasil dihapus.']);
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
                'sort_order' => $img->sort_order,
            ])->values(),
        ]);
    }
}
