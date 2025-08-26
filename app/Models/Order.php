<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'restaurant_id',
        'delivery_person_id',
        'status',
        'payment_method',
        'payment_status',
        'payment_reference',
        'subtotal',
        'delivery_fee',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'delivery_address',
        'notes',
        'estimated_delivery_time',
        'delivered_at',
        'confirmed_at',
        'preparing_at',
        'ready_at',
        'picked_up_at',
        'cancelled_at',
        'delivery_latitude',
        'delivery_longitude',
        'location_updated_at',
        'cancel_reason'
    ];

    protected $casts = [
        'delivery_address' => 'array',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
        'confirmed_at' => 'datetime',
        'preparing_at' => 'datetime',
        'ready_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'location_updated_at' => 'datetime',
        'estimated_delivery_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
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

 // === RELACIONAMENTOS ===




    /**
     * Pedidos por status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Pedidos pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Pedidos confirmados
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Pedidos em preparação
     */
    public function scopePreparing($query)
    {
        return $query->where('status', 'preparing');
    }

    /**
     * Pedidos prontos para entrega
     */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    /**
     * Pedidos em entrega
     */
    public function scopeInDelivery($query)
    {
        return $query->where('status', 'picked_up');
    }

    /**
     * Pedidos entregues
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Pedidos cancelados
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Pedidos ativos (não finalizados)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'picked_up']);
    }

    /**
     * Pedidos de hoje
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Pedidos disponíveis para entrega
     */
    public function scopeAvailableForDelivery($query)
    {
        return $query->where('status', 'ready')
                    ->whereNull('delivery_person_id')
                    ->where('payment_status', 'paid');
    }

    // === MÉTODOS DE CONVENIÊNCIA ===



    /**
     * Verifica se o pedido pode ser rastreado
     */
    public function canBeTracked(): bool
    {
        return in_array($this->status, ['confirmed', 'preparing', 'ready', 'picked_up']);
    }

    /**
     * Verifica se o pedido está ativo
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'preparing', 'ready', 'picked_up']);
    }

    /**
     * Verifica se o pedido foi finalizado
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['delivered', 'cancelled']);
    }

    /**
     * Verifica se o pedido precisa de pagamento
     */
    public function needsPayment(): bool
    {
        return $this->payment_status !== 'paid';
    }

    /**
     * Obter texto do status em português
     */
    public function getStatusText(): string
    {
        $statusTexts = [
            'pending' => 'Aguardando Pagamento',
            'confirmed' => 'Confirmado',
            'preparing' => 'Preparando',
            'ready' => 'Pronto',
            'picked_up' => 'Saiu para Entrega',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelado'
        ];

        return $statusTexts[$this->status] ?? 'Status Desconhecido';
    }

    /**
     * Obter cor do status
     */
    public function getStatusColor(): string
    {
        $statusColors = [
            'pending' => '#F59E0B',     // warning
            'confirmed' => '#00C896',   // accent
            'preparing' => '#FFB800',   // secondary
            'ready' => '#10B981',       // success
            'picked_up' => '#FF6B35',   // primary
            'delivered' => '#10B981',   // success
            'cancelled' => '#EF4444'    // error
        ];

        return $statusColors[$this->status] ?? '#6B7280';
    }

    /**
     * Calcular tempo estimado de entrega
     */
    public function calculateEstimatedDeliveryTime(): ?\Carbon\Carbon
    {
        if ($this->estimated_delivery_time) {
            return $this->estimated_delivery_time;
        }

        $restaurant = $this->restaurant;
        if (!$restaurant) {
            return null;
        }

        // Estimar baseado no tempo de preparo + entrega
        $prepareTime = ($restaurant->delivery_time_min ?? 20) + 10; // 10 min para entrega

        return $this->created_at->addMinutes($prepareTime);
    }

    /**
     * Formatar endereço de entrega
     */
    public function getFormattedAddress(): string
    {
        if (!$this->delivery_address) {
            return 'Endereço não informado';
        }

        $address = $this->delivery_address;

        if (is_string($address)) {
            return $address;
        }

        if (is_array($address)) {
            $parts = [];

            if (!empty($address['street'])) {
                $parts[] = $address['street'];
            }

            if (!empty($address['city'])) {
                $parts[] = $address['city'];
            }

            return implode(', ', $parts) ?: 'Endereço não informado';
        }

        return 'Endereço não informado';
    }

    /**
     * Obter próximo status possível
     */
    public function getNextStatus(): ?string
    {
        $statusFlow = [
            'pending' => 'confirmed',
            'confirmed' => 'preparing',
            'preparing' => 'ready',
            'ready' => 'picked_up',
            'picked_up' => 'delivered'
        ];

        return $statusFlow[$this->status] ?? null;
    }

    /**
     * Atualizar para próximo status
     */
    public function advanceToNextStatus(): bool
    {
        $nextStatus = $this->getNextStatus();

        if (!$nextStatus) {
            return false;
        }

        return $this->updateStatus($nextStatus);
    }

    /**
     * Atualizar status com timestamp apropriado
     */
    public function updateStatus(string $status): bool
    {
        $updateData = ['status' => $status];

        // Adicionar timestamp baseado no status
        switch ($status) {
            case 'confirmed':
                $updateData['confirmed_at'] = now();
                break;
            case 'preparing':
                $updateData['preparing_at'] = now();
                break;
            case 'ready':
                $updateData['ready_at'] = now();
                break;
            case 'picked_up':
                $updateData['picked_up_at'] = now();
                break;
            case 'delivered':
                $updateData['delivered_at'] = now();
                $updateData['payment_status'] = 'paid'; // Auto-marcar como pago
                break;
            case 'cancelled':
                $updateData['cancelled_at'] = now();
                break;
        }

        return $this->update($updateData);
    }

    // === ACCESSORS ===

    /**
     * Accessor para total formatado
     */
    public function getTotalFormattedAttribute(): string
    {
        return number_format($this->total_amount, 2, ',', '.') . ' MT';
    }

    /**
     * Accessor para endereço formatado
     */
    public function getDeliveryAddressFormattedAttribute(): string
    {
        return $this->getFormattedAddress();
    }

}
