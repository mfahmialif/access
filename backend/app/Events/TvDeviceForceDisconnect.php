<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TvDeviceForceDisconnect implements ShouldBroadcastNow
{
    public int $device_id;
    public string $token;

    public function __construct(int $device_id, string $token)
    {
        $this->device_id = $device_id;
        $this->token = $token;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('tv-devices');
    }

    public function broadcastAs(): string
    {
        return 'device.deleted';
    }
}
