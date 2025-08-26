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
            'delivery_address' => 'array', // CRÍTICO: Permite JSON no campo
            'estimated_delivery_time' => 'datetime',
            'delivered_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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

    // =============== RELACIONAMENTOS ===============

    /**
     * Cliente que fez o pedido
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Usuário alternativo (mesmo que customer, mas mais claro)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Restaurante do pedido
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    /**
     * Entregador responsável pelo pedido
     */
    public function deliveryPerson()
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    /**
     * Itens do pedido
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Pagamentos relacionados ao pedido
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Último pagamento
     */
    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    // =============== SCOPES PARA ENTREGADORES ===============

    /**
     * Pedidos disponíveis para entregadores
     */
    public function scopeAvailableForDelivery($query)
    {
        return $query->where('status', 'ready')
                     ->whereNull('delivery_person_id')
                     ->where('payment_status', 'paid');
    }

    /**
     * Pedidos próximos a uma localização (para uso com coordenadas)
     */
    public function scopeNearLocation($query, $latitude, $longitude, $radiusKm = 5)
    {
        $earthRadius = 6371; // Raio da Terra em km

        return $query->join('restaurants', 'orders.restaurant_id', '=', 'restaurants.id')
                     ->whereNotNull('restaurants.latitude')
                     ->whereNotNull('restaurants.longitude')
                     ->selectRaw("
                         orders.*,
                         ($earthRadius * acos(
                             cos(radians(?)) *
                             cos(radians(restaurants.latitude)) *
                             cos(radians(restaurants.longitude) - radians(?)) +
                             sin(radians(?)) *
                             sin(radians(restaurants.latitude))
                         )) as distance_km
                     ", [$latitude, $longitude, $latitude])
                     ->havingRaw('distance_km <= ?', [$radiusKm])
                     ->orderBy('distance_km', 'asc');
    }

    /**
     * Pedidos de um entregador específico
     */
    public function scopeForDeliveryPerson($query, $deliveryPersonId)
    {
        return $query->where('delivery_person_id', $deliveryPersonId);
    }

    /**
     * Pedidos pagos
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Pedidos por status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // =============== MÉTODOS DE STATUS ===============

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

    // =============== MÉTODOS DE PAGAMENTO ===============

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isPendingPayment()
    {
        return $this->payment_status === 'pending';
    }

    public function requiresCashPayment()
    {
        return $this->payment_method === 'cash' && $this->payment_status === 'pending';
    }

    // =============== MÉTODOS DE VALIDAÇÃO ===============

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canBeAcceptedByDeliveryPerson()
    {
        return $this->status === 'ready'
               && is_null($this->delivery_person_id)
               && $this->isPaid();
    }

    public function canBePickedUp()
    {
        return $this->status === 'ready' && $this->delivery_person_id;
    }

    public function canBeDelivered()
    {
        return $this->status === 'picked_up' && $this->delivery_person_id;
    }

    // =============== MÉTODOS DE APOIO ===============

    /**
     * Atribuir entregador ao pedido
     */
    public function assignDeliveryPerson($deliveryPersonId)
    {
        if (!$this->canBeAcceptedByDeliveryPerson()) {
            return false;
        }

        $this->update([
            'delivery_person_id' => $deliveryPersonId,
            'status' => 'picked_up'
        ]);

        return true;
    }

    /**
     * Marcar como entregue
     */
    public function markAsDelivered()
    {
        if (!$this->canBeDelivered()) {
            return false;
        }

        $this->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);

        return true;
    }

    /**
     * Obter endereço de entrega formatado
     */
    public function getFormattedDeliveryAddress()
    {
        if (!$this->delivery_address) {
            return 'Endereço não informado';
        }

        if (is_string($this->delivery_address)) {
            return $this->delivery_address;
        }

        if (is_array($this->delivery_address)) {
            $parts = array_filter([
                $this->delivery_address['street'] ?? '',
                $this->delivery_address['number'] ?? '',
                $this->delivery_address['neighborhood'] ?? '',
                $this->delivery_address['city'] ?? ''
            ]);

            return implode(', ', $parts);
        }

        return 'Endereço inválido';
    }

    /**
     * Obter coordenadas de entrega
     */
    public function getDeliveryCoordinates()
    {
        if (!$this->delivery_address || !is_array($this->delivery_address)) {
            return null;
        }

        $lat = $this->delivery_address['latitude'] ?? null;
        $lng = $this->delivery_address['longitude'] ?? null;

        if ($lat && $lng) {
            return [
                'latitude' => (float) $lat,
                'longitude' => (float) $lng
            ];
        }

        return null;
    }

    /**
     * Verificar se o pedido tem coordenadas de entrega
     */
    public function hasDeliveryCoordinates()
    {
        return !is_null($this->getDeliveryCoordinates());
    }

    // =============== ACESSORS E MUTATORS ===============

    /**
     * Accessor para status em português
     */
    public function getStatusTextAttribute()
    {
        $statusMap = [
            'pending' => 'Pendente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Preparando',
            'ready' => 'Pronto',
            'picked_up' => 'Coletado',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelado'
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    /**
     * Accessor para método de pagamento em português
     */
    public function getPaymentMethodTextAttribute()
    {
        $paymentMap = [
            'cash' => 'Dinheiro',
            'mpesa' => 'M-Pesa',
            'mola' => 'e-Mola',
            'card' => 'Cartão'
        ];

        return $paymentMap[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Accessor para valor total formatado
     */
    public function getFormattedTotalAttribute()
    {
        return 'MT ' . number_format($this->total_amount, 2);
    }

    // =============== MÉTODOS DE DEBUG ===============

    /**
     * Debug info para entregadores
     */
    public function getDeliveryDebugInfo()
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'delivery_person_id' => $this->delivery_person_id,
            'restaurant_id' => $this->restaurant_id,
            'restaurant_name' => $this->restaurant->name ?? 'N/A',
            'restaurant_coordinates' => [
                'latitude' => $this->restaurant->latitude ?? null,
                'longitude' => $this->restaurant->longitude ?? null
            ],
            'customer_name' => $this->customer->name ?? 'N/A',
            'total_amount' => $this->total_amount,
            'created_at' => $this->created_at,
            'can_be_accepted' => $this->canBeAcceptedByDeliveryPerson(),
            'has_delivery_coords' => $this->hasDeliveryCoordinates(),
            'delivery_address' => $this->getFormattedDeliveryAddress()
        ];
    }
}
