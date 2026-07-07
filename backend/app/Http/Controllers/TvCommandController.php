<?php

namespace App\Http\Controllers;

use App\Events\TvCommand;
use App\Models\ActiveBanner;
use App\Models\TvCommandLog;
use App\Models\TvDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TvCommandController extends Controller
{
    /**
     * Safely broadcast — if Soketi is down, don't break the API.
     */
    private function safeBroadcast(TvCommand $event): bool
    {
        try {
            broadcast($event);
            return true;
        } catch (\Throwable $e) {
            Log::warning('TV Command broadcast failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log the command.
     */
    private function logCommand(Request $request, string $command, string $target, array $payload = []): TvCommandLog
    {
        return TvCommandLog::create([
            'command' => $command,
            'target'  => $target,
            'payload' => $payload,
            'status'  => 'sent',
            'user_id' => $request->user()?->id,
        ]);
    }

    /**
     * POST /tv-commands/push — Push content to specific TV
     */
    public function push(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:tv_devices,id',
            'path'      => 'required|string|max:500',
            'label'     => 'nullable|string|max:255',
        ]);

        $device = TvDevice::findOrFail($request->device_id);

        $payload = [
            'path'  => $request->path,
            'label' => $request->label ?? 'Konten',
        ];

        $event = new TvCommand('navigate', $device->token, $device->id, $payload);
        $sent = $this->safeBroadcast($event);

        $log = $this->logCommand($request, 'navigate', $device->name, $payload);
        $log->update(['status' => $sent ? 'sent' : 'failed']);

        return response()->json([
            'message' => $sent ? "Konten berhasil dikirim ke {$device->name}" : 'Gagal mengirim — broadcast server tidak aktif',
            'success' => $sent,
            'log'     => $log,
        ]);
    }

    /**
     * POST /tv-commands/broadcast — Push content to all online TVs
     */
    public function broadcast(Request $request)
    {
        $request->validate([
            'path'  => 'required|string|max:500',
            'label' => 'nullable|string|max:255',
        ]);

        $onlineCount = TvDevice::where('status', 'online')->count();

        $payload = [
            'path'  => $request->path,
            'label' => $request->label ?? 'Konten',
        ];

        // target_token = null means all devices
        $event = new TvCommand('navigate', null, null, $payload);
        $sent = $this->safeBroadcast($event);

        $log = $this->logCommand($request, 'navigate', "Semua TV ({$onlineCount} online)", $payload);
        $log->update(['status' => $sent ? 'sent' : 'failed']);

        return response()->json([
            'message' => $sent ? "Broadcast berhasil ke {$onlineCount} TV online" : 'Gagal — broadcast server tidak aktif',
            'success' => $sent,
            'log'     => $log,
        ]);
    }

    /**
     * POST /tv-commands/reload — Force reload target TV(s)
     */
    public function reload(Request $request)
    {
        $request->validate([
            'target' => 'required|string', // "all" or device ID
        ]);

        $targetName = 'Semua TV';
        $targetToken = null;
        $targetId = null;

        if ($request->target !== 'all') {
            $device = TvDevice::findOrFail($request->target);
            $targetName = $device->name;
            $targetToken = $device->token;
            $targetId = $device->id;
        }

        $event = new TvCommand('reload', $targetToken, $targetId, []);
        $sent = $this->safeBroadcast($event);

        $log = $this->logCommand($request, 'reload', $targetName);
        $log->update(['status' => $sent ? 'sent' : 'failed']);

        return response()->json([
            'message' => $sent ? "Force Reload dikirim ke {$targetName}" : 'Gagal — broadcast server tidak aktif',
            'success' => $sent,
            'log'     => $log,
        ]);
    }

    /**
     * POST /tv-commands/home — Force navigate to home page
     */
    public function home(Request $request)
    {
        $request->validate([
            'target' => 'required|string',
        ]);

        $targetName = 'Semua TV';
        $targetToken = null;
        $targetId = null;

        if ($request->target !== 'all') {
            $device = TvDevice::findOrFail($request->target);
            $targetName = $device->name;
            $targetToken = $device->token;
            $targetId = $device->id;
        }

        $event = new TvCommand('home', $targetToken, $targetId, ['path' => '/']);
        $sent = $this->safeBroadcast($event);

        $log = $this->logCommand($request, 'home', $targetName, ['path' => '/']);
        $log->update(['status' => $sent ? 'sent' : 'failed']);

        return response()->json([
            'message' => $sent ? "Force Home dikirim ke {$targetName}" : 'Gagal — broadcast server tidak aktif',
            'success' => $sent,
            'log'     => $log,
        ]);
    }

    /**
     * POST /tv-commands/banner — Show or dismiss global banner
     */
    public function banner(Request $request)
    {
        $request->validate([
            'action'  => 'required|in:show,dismiss',
            'title'   => 'required_if:action,show|nullable|string|max:255',
            'message' => 'required_if:action,show|nullable|string|max:2000',
            'type'    => 'nullable|in:emergency,announcement,info',
        ]);

        $command = $request->action === 'show' ? 'banner_show' : 'banner_dismiss';

        $payload = $request->action === 'show' ? [
            'title'   => $request->title,
            'message' => $request->message,
            'type'    => $request->input('type', 'info'),
        ] : [];

        // Persist banner state in DB (tv_device_id = null → global)
        if ($request->action === 'show') {
            ActiveBanner::updateOrCreate(
                ['tv_device_id' => null],
                [
                    'title'      => $request->title,
                    'message'    => $request->message,
                    'type'       => $request->input('type', 'info'),
                    'created_by' => $request->user()?->id,
                ]
            );
        } else {
            ActiveBanner::whereNull('tv_device_id')->delete();
        }

        $event = new TvCommand($command, null, null, $payload);
        $sent = $this->safeBroadcast($event);

        $label = $request->action === 'show'
            ? "Banner \"{$request->title}\" ditampilkan di semua TV"
            : 'Banner aktif dihapus dari semua TV';

        $log = $this->logCommand($request, $command, 'Semua TV', $payload);
        $log->update(['status' => $sent ? 'sent' : 'failed']);

        return response()->json([
            'message' => $sent ? $label : 'Gagal — broadcast server tidak aktif',
            'success' => $sent,
            'log'     => $log,
        ]);
    }

    /**
     * GET /tv-commands/active-banner — Get current active banner (if any)
     * Accepts optional ?token= and/or ?device_id= to check device-specific banners.
     */
    public function activeBanner(Request $request)
    {
        // Check global banner first
        $banner = ActiveBanner::whereNull('tv_device_id')->first();

        // If no global banner, check device-specific banner
        if (!$banner) {
            $device = null;
            if ($request->filled('device_id')) {
                $device = TvDevice::find($request->device_id);
            } elseif ($request->filled('token')) {
                $device = TvDevice::where('token', $request->token)->first();
            }

            if ($device) {
                $banner = ActiveBanner::where('tv_device_id', $device->id)->first();
            }
        }

        $data = $banner ? [
            'title'   => $banner->title,
            'message' => $banner->message,
            'type'    => $banner->type,
        ] : null;

        return response()->json([
            'active'  => !is_null($banner),
            'banner'  => $data,
        ]);
    }

    /**
     * POST /tv-commands/targeted-banner — Show or dismiss banner for specific TVs
     */
    public function targetedBanner(Request $request)
    {
        $request->validate([
            'action'     => 'required|in:show,dismiss',
            'device_ids' => 'required|array|min:1',
            'device_ids.*' => 'exists:tv_devices,id',
            'title'      => 'required_if:action,show|nullable|string|max:255',
            'message'    => 'required_if:action,show|nullable|string|max:5000',
            'type'       => 'nullable|in:emergency,announcement,info',
        ]);

        $devices = TvDevice::whereIn('id', $request->device_ids)->get();
        $command = $request->action === 'show' ? 'banner_show' : 'banner_dismiss';
        $sentCount = 0;

        $payload = $request->action === 'show' ? [
            'title'   => $request->title,
            'message' => $request->message,
            'type'    => $request->input('type', 'info'),
        ] : [];

        foreach ($devices as $device) {
            if ($request->action === 'show') {
                ActiveBanner::updateOrCreate(
                    ['tv_device_id' => $device->id],
                    [
                        'title'      => $request->title,
                        'message'    => $request->message,
                        'type'       => $request->input('type', 'info'),
                        'created_by' => $request->user()?->id,
                    ]
                );
            } else {
                ActiveBanner::where('tv_device_id', $device->id)->delete();
            }

            $event = new TvCommand($command, $device->token, $device->id, $payload);
            if ($this->safeBroadcast($event)) $sentCount++;
        }

        $names = $devices->pluck('name')->implode(', ');
        $label = $request->action === 'show'
            ? "Banner ditampilkan di {$sentCount} TV: {$names}"
            : "Banner dihapus dari {$sentCount} TV: {$names}";

        $this->logCommand($request, $command, $names, $payload);

        return response()->json([
            'message' => $label,
            'success' => $sentCount > 0,
        ]);
    }

    /**
     * GET /tv-commands/targeted-banners — List all active targeted banners
     */
    public function targetedBanners()
    {
        $banners = ActiveBanner::whereNotNull('tv_device_id')
            ->with('tvDevice:id,name')
            ->get();

        return response()->json($banners->map(fn ($b) => [
            'device_id'   => $b->tv_device_id,
            'device_name' => $b->tvDevice?->name ?? 'Unknown',
            'banner'      => [
                'title'   => $b->title,
                'message' => $b->message,
                'type'    => $b->type,
            ],
        ]));
    }

    /**
     * GET /tv-commands/all-active-banners — List ALL active banners (global + targeted)
     */
    public function allActiveBanners()
    {
        $banners = ActiveBanner::with('tvDevice:id,name', 'creator:id,name')
            ->orderByRaw('tv_device_id IS NOT NULL, tv_device_id')
            ->get();

        return response()->json($banners->map(fn ($b) => [
            'id'          => $b->id,
            'tv_device_id' => $b->tv_device_id,
            'device_name' => $b->tvDevice?->name ?? null,
            'title'       => $b->title,
            'message'     => $b->message,
            'type'        => $b->type,
            'scope'       => $b->tv_device_id ? 'targeted' : 'global',
            'created_by'  => $b->creator?->name ?? '-',
            'created_at'  => $b->created_at?->toDateTimeString(),
        ]));
    }

    /**
     * PUT /tv-commands/active-banners/{id} — Update an active banner
     */
    public function updateBanner(Request $request, $id)
    {
        $banner = ActiveBanner::findOrFail($id);

        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'type'    => 'nullable|in:emergency,announcement,info',
        ]);

        $banner->update([
            'title'   => $request->title,
            'message' => $request->message,
            'type'    => $request->input('type', $banner->type),
        ]);

        // Re-broadcast updated banner
        $payload = ['title' => $banner->title, 'message' => $banner->message, 'type' => $banner->type];
        if ($banner->tv_device_id) {
            $device = TvDevice::find($banner->tv_device_id);
            if ($device) {
                $this->safeBroadcast(new TvCommand('banner_show', $device->token, $device->id, $payload));
            }
        } else {
            $this->safeBroadcast(new TvCommand('banner_show', null, null, $payload));
        }

        return response()->json(['message' => 'Banner berhasil diperbarui', 'banner' => $banner]);
    }

    /**
     * DELETE /tv-commands/active-banners/{id} — Delete an active banner
     */
    public function deleteBanner($id)
    {
        $banner = ActiveBanner::findOrFail($id);

        // Broadcast dismiss
        if ($banner->tv_device_id) {
            $device = TvDevice::find($banner->tv_device_id);
            if ($device) {
                $this->safeBroadcast(new TvCommand('banner_dismiss', $device->token, $device->id, []));
            }
        } else {
            $this->safeBroadcast(new TvCommand('banner_dismiss', null, null, []));
        }

        $banner->delete();
        return response()->json(['message' => 'Banner berhasil dihapus']);
    }

    /**
     * GET /tv-commands/logs — Get command log history
     */
    public function logs(Request $request)
    {
        $query = TvCommandLog::with('user:id,name')
            ->orderByDesc('created_at');

        if ($request->filled('limit')) {
            return $query->limit($request->limit)->get();
        }

        return $query->paginate($request->input('per_page', 20));
    }
}
