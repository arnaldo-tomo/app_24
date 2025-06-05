<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'price', 'discount_price', 'image', 'images',
        'is_vegetarian', 'is_vegan', 'is_spicy', 'is_available', 'is_featured',
        'preparation_time', 'calories', 'allergens', 'ingredients', 'sort_order',
        'restaurant_id', 'menu_category_id'
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'images' => 'array',
            'is_vegetarian' => 'boolean',
            'is_vegan' => 'boolean',
            'is_spicy' => 'boolean',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'allergens' => 'array',
            'ingredients' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($menuItem) {
            if (empty($menuItem->slug)) {
                $menuItem->slug = Str::slug($menuItem->name);
            }
        });
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getFinalPrice()
    {
        return $this->discount_price ?? $this->price;
    }

    public function hasDiscount()
    {
        return !is_null($this->discount_price) && $this->discount_price < $this->price;
    }

    public function getDiscountPercentage()
    {
        if (!$this->hasDiscount()) {
            return 0;
        }

        return round((($this->price - $this->discount_price) / $this->price) * 100);
    }
}