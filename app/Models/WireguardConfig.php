<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WireguardConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'port_id',
        'user_id',
        'private_key',
        'public_key',
        'preshared_key',
        'client_ip',
        'server_public_key',
        'server_endpoint',
        'server_port',
        'config_file',
        'dns',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function port()
    {
        return $this->belongsTo(Port::class);
    }

    public function getConfigForDownloadAttribute(): string
    {
        return $this->config_file;
    }

    public function getQrCodeDataAttribute(): string
    {
        return $this->config_file;
    }
}
