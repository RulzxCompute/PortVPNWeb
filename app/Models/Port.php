<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'node_id',
        'public_port',
        'local_port',
        'protocol',
        'vpn_type',
        'ssh_enabled',
        'ssh_port',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'ssh_enabled' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function wireguardConfig()
    {
        return $this->hasOne(WireguardConfig::class);
    }

    public function sstpConfig()
    {
        return $this->hasOne(SstpConfig::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }
}
