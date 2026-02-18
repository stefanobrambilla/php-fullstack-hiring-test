<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Travel extends Model
{
    public const MAX_SEATS = 5;

    protected $table = 'travels';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'slug',
        'name',
        'description',
        'starting_date',
        'ending_date',
        'price',
        'moods',
    ];

    protected function casts(): array
    {
        return [
            'starting_date' => 'date',
            'ending_date' => 'date',
            'moods' => 'array',
        ];
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function seatsTaken(): int
    {
        $now = now();

        return (int) $this->carts()
            ->where(function ($query) use ($now): void {
                $query->where('status', Cart::STATUS_PAID)
                    ->orWhere(function ($query) use ($now): void {
                        $query->where('status', Cart::STATUS_PENDING)
                            ->where('expires_at', '>', $now);
                    });
            })
            ->sum('seats');
    }

    public function seatsLeft(): int
    {
        return max(0, self::MAX_SEATS - $this->seatsTaken());
    }
}
