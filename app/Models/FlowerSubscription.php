<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlowerSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'frequency',
        'delivery_day',
        'preferred_color_palette',
        'status',
        'amount',
        'paused_at',
        'cancelled_at',
        'next_delivery_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paused_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'next_delivery_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }
}
