<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'stream_url',
        'thumbnail',
        'is_active',
        'scheduled_at',
        'started_at',
        'ended_at',
        'viewer_count'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    // Scope pour les streams actifs
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope pour les streams en direct
    public function scopeLive($query)
    {
        return $query->where('is_active', true)
                    ->whereNotNull('started_at')
                    ->whereNull('ended_at');
    }

    // Accesseur pour vérifier si le stream est en direct
    public function getIsLiveAttribute()
    {
        return $this->is_active &&
               !is_null($this->started_at) &&
               is_null($this->ended_at);
    }
}
