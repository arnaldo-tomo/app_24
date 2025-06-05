<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'phone', 'email', 'address',
        'latitude', 'longitude', 'image', 'cover_image', 'delivery_fee',
        'delivery_time_min', 'delivery_time_max', 'minimum_order',
        'rating', 'total_reviews', 'is_active', 'is_featured',
        'opening_time', 'closing_time', 'working_days', 'user_id'
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'delivery_fee' => 'decimal:2',
            'minimum_order' => 'decimal:2',
            'rating' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'working_days' => 'array',
            'opening_time' => 'datetime:H:i',
            'closing_time' => 'datetime:H:i',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($restaurant) {
            if (empty($restaurant->slug)) {
                $restaurant->slug = Str::slug($restaurant->name);
            }
        });
    }

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'restaurant_categories');
    }

    public function menuCategories()
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Helper methods
    public function isOpen()
    {
        $now = now();
        $currentDay = strtolower($now->format('l'));

        if (!in_array($currentDay, $this->working_days)) {
            return false;
        }

        $currentTime = $now->format('H:i');
        return $currentTime >= $this->opening_time->format('H:i') &&
               $currentTime <= $this->closing_time->format('H:i');
    }

    public function getAverageDeliveryTime()
    {
        return ($this->delivery_time_min + $this->delivery_time_max) / 2;
    }
}