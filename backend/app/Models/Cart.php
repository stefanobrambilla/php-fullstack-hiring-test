<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Cart extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_EXPIRED = 'expired';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'travel_id',
        'email',
        'seats',
        'status',
        'expires_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cart $cart): void {
            if (! $cart->id) {
                $cart->id = (string) Str::uuid();
            }
        });
    }

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }

    public function scopeBooked(Builder $query): void
    {
        $now = now();

        $query->where(function ($query) use ($now): void {
            $query->where('status', self::STATUS_PAID)
                ->orWhere(function ($query) use ($now): void {
                    $query->where('status', self::STATUS_PENDING)
                        ->where('expires_at', '>', $now);
                });
        });
    }

    public function markExpiredIfNeeded(): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            return;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            $this->status = self::STATUS_EXPIRED;
            $this->save();
        }
    }
}
