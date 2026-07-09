<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'logo_path', 'status',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class);
    }

    public function weeklies(): HasMany
    {
        return $this->hasMany(Weekly::class);
    }

    public function monthlies(): HasMany
    {
        return $this->hasMany(Monthly::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function appLinks(): HasMany
    {
        return $this->hasMany(AppLink::class);
    }

    public function tvDevices(): HasMany
    {
        return $this->hasMany(TvDevice::class);
    }
}
