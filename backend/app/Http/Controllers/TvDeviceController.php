<?php

namespace App\Http\Controllers;

use App\Events\TvDeviceForceDisconnect;
use App\Events\TvDeviceStatusChanged;
use App\Models\TvDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TvDeviceController extends Controller
{
    /**
     * Auto-mark devices as offline if heartbeat is stale (>2 minutes).
     * Heartbeat interval is 10s, so 2-min threshold tolerates brief network drops.
     */
    private function refreshStatuses(): void
    {
        $staleDevices = TvDevice::where('status', 'online')
            ->where('last_heartbeat', '<', now()->subMinutes(3))
            ->get();

        foreach ($staleDevices as $device) {
            $device->update(['status' => 'offline']);
            $this->safeBroadcast($device->fresh());
        }
    }

    /**
     * Broadcast safely — if Soketi is down, don't break the API.
     */
    private function safeBroadcast(TvDevice $device): void
    {
        try {
            broadcast(new TvDeviceStatusChanged($device));
        } catch (\Throwable $e) {
            // Soketi not running — log but don't crash
            Log::warning('Broadcast failed: ' . $e->getMessage());
        }
    }

    // ── Admin: list all devices ──
    public function index(Request $request)
    {
        $this->refreshStatuses();

        $query = TvDevice::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('location', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('orientation')) {
            $query->where('orientation', $request->orientation);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowed = ['created_at', 'name', 'status', 'last_heartbeat'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderByDesc('created_at');
        }

        $perPage = $request->input('per_page', 12);

        // Eager-load per-device banner
        $query->with('activeBanner:id,tv_device_id,title,type');

        // Also check if there's a global banner
        $globalBanner = \App\Models\ActiveBanner::whereNull('tv_device_id')->first();

        $result = $query->paginate($perPage);

        // Append global banner info to each page response
        return response()->json(
            array_merge($result->toArray(), [
                'global_banner' => $globalBanner ? [
                    'title' => $globalBanner->title,
                    'type'  => $globalBanner->type,
                ] : null,
            ])
        );
    }

    // ── Admin: get real-time current page of a device ──
    public function currentPage(TvDevice $tvDevice)
    {
        return response()->json([
            'current_page' => $tvDevice->current_page,
        ]);
    }

    // ── Admin: stats ──
    public function stats()
    {
        $this->refreshStatuses();

        return response()->json([
            'total'   => TvDevice::count(),
            'online'  => TvDevice::where('status', 'online')->count(),
            'offline' => TvDevice::where('status', 'offline')->count(),
            'setup'   => TvDevice::where('status', 'setup')->count(),
            'warning' => TvDevice::whereNotNull('warning_message')->count(),
        ]);
    }

    // ── Admin: create device ──
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'location'    => 'nullable|string|max:255',
            'orientation' => 'nullable|in:landscape,portrait',
        ]);

        $device = TvDevice::create([
            'name'          => $request->name,
            'location'      => $request->location,
            'orientation'   => $request->input('orientation', 'landscape'),
            'token'         => TvDevice::generateToken(),
            'status'        => 'setup',
            'registered_by' => $request->user()?->id,
        ]);

        return response()->json($device, 201);
    }

    // ── Admin: update device ──
    public function update(Request $request, TvDevice $tvDevice)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'location'        => 'nullable|string|max:255',
            'orientation'     => 'nullable|in:landscape,portrait',
            'warning_message' => 'nullable|string|max:500',
        ]);

        $tvDevice->update($request->only([
            'name', 'location', 'orientation', 'warning_message',
        ]));

        return response()->json($tvDevice);
    }

    // ── Admin: delete device ──
    public function destroy(TvDevice $tvDevice)
    {
        // Broadcast force-disconnect before deleting
        try {
            broadcast(new TvDeviceForceDisconnect($tvDevice->id, $tvDevice->token));
        } catch (\Throwable $e) {
            Log::warning('Broadcast failed: ' . $e->getMessage());
        }

        $tvDevice->delete();
        return response()->json(['message' => 'Perangkat berhasil dihapus.']);
    }

    // ── Admin: force disconnect device (without deleting) ──
    public function forceDisconnect(TvDevice $tvDevice)
    {
        try {
            broadcast(new TvDeviceForceDisconnect($tvDevice->id, $tvDevice->token));
        } catch (\Throwable $e) {
            Log::warning('Broadcast failed: ' . $e->getMessage());
        }

        $tvDevice->update([
            'status'       => 'setup',
            'current_page' => null,
        ]);

        // Also remove any active banners for this device
        \App\Models\ActiveBanner::where('tv_device_id', $tvDevice->id)->delete();

        return response()->json(['message' => "Sambungan {$tvDevice->name} berhasil diputuskan."]);
    }

    // ── Admin: regenerate token ──
    public function regenerateToken(TvDevice $tvDevice)
    {
        $tvDevice->update([
            'token'  => TvDevice::generateToken(),
            'status' => 'setup',
        ]);

        return response()->json($tvDevice);
    }

    // ── Public: TV connects with token ──
    public function connect(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:6',
        ]);

        $device = TvDevice::where('token', $request->token)->first();

        if (!$device) {
            return response()->json(['message' => 'Token tidak valid.'], 404);
        }

        $device->update([
            'status'         => 'online',
            'ip_address'     => $request->ip(),
            'last_heartbeat' => now(),
        ]);

        $this->safeBroadcast($device->fresh());

        return response()->json([
            'message' => 'Terhubung berhasil.',
            'device'  => $device,
        ]);
    }

    // ── Public: TV heartbeat ──
    public function heartbeat(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:6',
        ]);

        $device = TvDevice::where('token', $request->token)->first();

        if (!$device) {
            return response()->json(['message' => 'Token tidak valid.'], 404);
        }

        $oldStatus = $device->status;
        $oldPage = $device->current_page;

        $device->update([
            'ip_address'       => $request->ip(),
            'last_heartbeat'   => now(),
            'firmware_version' => $request->input('firmware_version', $device->firmware_version),
            'current_page'     => $request->input('current_page', $device->current_page),
            'status'           => 'online',
        ]);

        // Broadcast if status or current_page changed
        if ($oldStatus !== 'online' || $oldPage !== $device->current_page) {
            $this->safeBroadcast($device->fresh());
        }

        return response()->json(['status' => 'ok']);
    }

    // ── Public: TV disconnects (browser close) ──
    public function disconnect(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:6',
        ]);

        $device = TvDevice::where('token', $request->token)->first();

        if (!$device) {
            return response()->json(['message' => 'Token tidak valid.'], 404);
        }

        $device->update(['status' => 'offline']);
        $this->safeBroadcast($device->fresh());

        return response()->json(['status' => 'disconnected']);
    }
}
