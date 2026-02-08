<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'ip_address',
        'location',
        'region',
        'port_start',
        'port_end',
        'total_ports',
        'used_ports',
        'is_active',
        'api_key',
        'ssl_enabled',
        'ping_ms',
        'last_ping_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ssl_enabled' => 'boolean',
        'last_ping_at' => 'datetime',
    ];

    public function ports()
    {
        return $this->hasMany(Port::class);
    }

    public function getAvailablePortsAttribute(): int
    {
        return $this->total_ports - $this->used_ports;
    }

    public function getApiUrlAttribute(): string
    {
        $protocol = $this->ssl_enabled ? 'https' : 'http';
        return "{$protocol}://{$this->domain}/api";
    }

    public function isAvailable(): bool
    {
        return $this->is_active && $this->available_ports > 0;
    }

    public function incrementUsedPorts(): void
    {
        $this->increment('used_ports');
    }

    public function decrementUsedPorts(): void
    {
        $this->decrement('used_ports');
    }
}
