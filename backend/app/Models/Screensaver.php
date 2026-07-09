<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Screensaver extends Model
{
    protected $fillable = [
        'unit_id',
        'tv_device_id',
        'idle_timeout',
        'interval',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'idle_timeout' => 'integer',
        'interval' => 'integer',
    ];

    public function tvDevice(): BelongsTo
    {
        return $this->belongsTo(TvDevice::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ScreensaverImage::class)->orderBy('sort_order');
    }
}
