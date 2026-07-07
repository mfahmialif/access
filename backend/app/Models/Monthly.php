<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Monthly extends Model
{
    protected $fillable = [
        'title', 'date', 'time', 'location', 'teacher', 'icon',
        'category', 'body', 'image_path', 'video_path',
        'status', 'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
