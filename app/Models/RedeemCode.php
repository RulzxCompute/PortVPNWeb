<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedeemCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'amount',
        'is_used',
        'used_by',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    public function isValid(): bool
    {
        if ($this->is_used) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function markAsUsed(int $userId): void
    {
        $this->update([
            'is_used' => true,
            'used_by' => $userId,
            'used_at' => now(),
        ]);
    }

    public function scopeValid($query)
    {
        return $query->where('is_used', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public static function generateCode(): string
    {
        return strtoupper(substr(md5(uniqid()), 0, 16));
    }
}
