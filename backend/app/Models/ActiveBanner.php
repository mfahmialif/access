<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveBanner extends Model
{
    protected $fillable = [
        'unit_id', 'tv_device_id', 'title', 'message', 'type', 'created_by',
    ];

    public function tvDevice(): BelongsTo
    {
        return $this->belongsTo(TvDevice::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
