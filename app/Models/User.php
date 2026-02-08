<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function ports()
    {
        return $this->hasMany(Port::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function wireguardConfigs()
    {
        return $this->hasMany(WireguardConfig::class);
    }

    public function sstpConfigs()
    {
        return $this->hasMany(SstpConfig::class);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function addBalance(int $amount): void
    {
        $this->increment('balance', $amount);
    }

    public function deductBalance(int $amount): bool
    {
        if ($this->balance < $amount) {
            return false;
        }
        $this->decrement('balance', $amount);
        return true;
    }
}
