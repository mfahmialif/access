<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TvCommand implements ShouldBroadcastNow
{
    public string $command;       // navigate, reload, home, banner_show, banner_dismiss
    public ?string $target_token; // null = all devices
    public ?int $target_id;       // null = all devices
    public array $payload;        // command-specific data

    public function __construct(string $command, ?string $target_token, ?int $target_id, array $payload = [])
    {
        $this->command = $command;
        $this->target_token = $target_token;
        $this->target_id = $target_id;
        $this->payload = $payload;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('tv-devices');
    }

    public function broadcastAs(): string
    {
        return 'tv.command';
    }
}
