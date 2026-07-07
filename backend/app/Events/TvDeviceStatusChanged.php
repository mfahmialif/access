<?php

namespace App\Events;

use App\Models\TvDevice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TvDeviceStatusChanged implements ShouldBroadcastNow
{
    public int $device_id;
    public string $name;
    public string $status;
    public ?string $ip_address;
    public ?string $last_heartbeat;
    public ?string $location;
    public ?string $orientation;
    public ?string $current_page;

    public function __construct(TvDevice $device)
    {
        $this->device_id = $device->id;
        $this->name = $device->name;
        $this->status = $device->status;
        $this->ip_address = $device->ip_address;
        $this->last_heartbeat = $device->last_heartbeat?->toISOString();
        $this->location = $device->location;
        $this->orientation = $device->orientation;
        $this->current_page = $device->current_page;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('tv-devices');
    }

    public function broadcastAs(): string
    {
        return 'status.changed';
    }
}
