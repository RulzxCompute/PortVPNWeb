<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'description',
        'reference_type',
        'reference_id',
        'balance_after',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDeposits($query)
    {
        return $query->where('type', 'deposit');
    }

    public function scopePurchases($query)
    {
        return $query->where('type', 'purchase');
    }

    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }

    public function isPurchase(): bool
    {
        return $this->type === 'purchase';
    }

    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->type === 'purchase' ? '-' : '+';
        return "{$sign}Rp " . number_format($this->amount, 0, ',', '.');
    }
}
