<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ScreensaverUpdated implements ShouldBroadcastNow
{
    public string $action;         // 'created', 'updated', 'deleted'
    public ?int $tv_device_id;     // null = default (all TVs)
    public ?int $screensaver_id;

    public function __construct(string $action, ?int $screensaverId = null, ?int $tvDeviceId = null)
    {
        $this->action = $action;
        $this->screensaver_id = $screensaverId;
        $this->tv_device_id = $tvDeviceId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('tv-devices');
    }

    public function broadcastAs(): string
    {
        return 'screensaver.updated';
    }
}
