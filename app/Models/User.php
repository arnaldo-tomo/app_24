<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'address',
        'latitude', 'longitude', 'avatar', 'is_active','push_token','device_platform','location_updated_at','delivery_radius_km','last_online_at'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_active' => 'boolean',
              'location_updated_at' => 'datetime',
    'last_online_at' => 'datetime',
        ];
    }

    // Role checks
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isRestaurantOwner()
    {
        return $this->role === 'restaurant_owner';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function isDeliveryPerson()
    {
        return $this->role === 'delivery_person';
    }

    // Relationships
    public function restaurants()
    {
        return $this->hasMany(Restaurant::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Order::class, 'delivery_person_id');
    }

    public function pushTokens()
{
    return $this->hasMany(PushToken::class);
}

public function notifications()
{
    return $this->hasMany(Notification::class);
}

public function unreadNotifications()
{
    return $this->hasMany(Notification::class)->where('is_read', false);
}
}
