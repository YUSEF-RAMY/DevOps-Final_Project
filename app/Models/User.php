<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function occasions(): HasMany
    {
        return $this->hasMany(Occasion::class);
    }

    public function flowerSubscriptions(): HasMany
    {
        return $this->hasMany(FlowerSubscription::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function totalSpent(): float
    {
        return (float) $this->orders()->where('payment_status', 'paid')->sum('total');
    }

    public function getDefaultBillingAddress()
    {
        return $this->addresses()->where('type', 'billing')->where('is_default', true)->first();
    }

    public function getDefaultShippingAddress()
    {
        return $this->addresses()->where('type', 'shipping')->where('is_default', true)->first();
    }
}
