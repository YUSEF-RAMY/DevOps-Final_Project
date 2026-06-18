<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Occasion extends Model
{
    protected $fillable = [
        'user_id',
        'recipient_name',
        'relation_type',
        'occasion_name',
        'occasion_date',
        'reminder_status',
        'reminder_days_before',
        'preferred_product_id',
        'preferred_bouquet_notes',
        'last_reminded_at',
    ];

    protected $casts = [
        'occasion_date' => 'date',
        'reminder_status' => 'boolean',
        'last_reminded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function preferredProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'preferred_product_id');
    }

    public function nextOccurrence(): Carbon
    {
        $today = now()->startOfDay();
        $occasion = $this->occasion_date->copy()->year($today->year);

        if ($occasion->lt($today)) {
            $occasion->addYear();
        }

        return $occasion;
    }

    public function daysUntil(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->nextOccurrence(), false);
    }

    public function isDueForReminder(): bool
    {
        if (!$this->reminder_status) {
            return false;
        }

        $daysUntil = $this->daysUntil();

        if ($daysUntil > $this->reminder_days_before) {
            return false;
        }

        if ($this->last_reminded_at && $this->last_reminded_at->isToday()) {
            return false;
        }

        return true;
    }

    public function displayLabel(): string
    {
        return sprintf(
            "%s's %s — %s",
            $this->recipient_name,
            $this->occasion_name,
            $this->nextOccurrence()->format('M j')
        );
    }
}
