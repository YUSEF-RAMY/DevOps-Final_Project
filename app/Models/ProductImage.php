<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'path',
        'position',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    protected $appends = ['image_path'];

    public function getImagePathAttribute(): ?string
    {
        return $this->path;
    }

    public function getUrlAttribute(): string
    {
        if ($this->path && str_starts_with($this->path, 'http')) {
            return $this->path;
        }

        return asset('storage/' . $this->path);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
