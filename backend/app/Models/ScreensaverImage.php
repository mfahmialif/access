<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreensaverImage extends Model
{
    protected $fillable = [
        'screensaver_id',
        'image_path',
        'media_type',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function screensaver(): BelongsTo
    {
        return $this->belongsTo(Screensaver::class);
    }
}
