<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TvDevice extends Model
{
    protected $fillable = [
        'name', 'location', 'orientation', 'token', 'status',
        'ip_address', 'last_heartbeat', 'firmware_version',
        'warning_message', 'current_page', 'registered_by',
    ];

    protected $casts = [
        'last_heartbeat' => 'datetime',
    ];

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function activeBanner(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\ActiveBanner::class);
    }

    /**
     * Generate a unique 6-digit numeric token.
     */
    public static function generateToken(): string
    {
        do {
            $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('token', $token)->exists());

        return $token;
    }
}
