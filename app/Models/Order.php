<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'status', 'subtotal', 'delivery_fee', 'tax_amount',
        'discount_amount', 'total_amount', 'payment_method', 'payment_status',
        'payment_reference', 'delivery_address', 'notes', 'estimated_delivery_time',
        'delivered_at', 'user_id', 'restaurant_id', 'delivery_person_id'
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'delivery_address' => 'array',
            'estimated_delivery_time' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function deliveryPerson()
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Status helpers
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    public function isPreparing()
    {
        return $this->status === 'preparing';
    }

    public function isReady()
    {
        return $this->status === 'ready';
    }

    public function isPickedUp()
    {
        return $this->status === 'picked_up';
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function payments()
{
    return $this->hasMany(Payment::class);
}

public function latestPayment()
{
    return $this->hasOne(Payment::class)->latest();
}

// Adicionar métodos úteis:
public function isPaid()
{
    return $this->payment_status === 'paid';
}

public function requiresCashPayment()
{
    return $this->payment_method === 'cash' && $this->payment_status === 'pending';
}
}
