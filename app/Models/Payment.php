<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'fee_amount',
        'net_amount',
        'status',
        'transaction_id',
        'external_transaction_id',
        'payment_date',
        'confirmed_at',
        'confirmation_data',
        'external_data',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'confirmation_data' => 'array',
        'external_data' => 'array'
    ];

    // === RELACIONAMENTOS ===

    /**
     * Pedido associado ao pagamento
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // === SCOPES ===

    /**
     * Pagamentos por status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Pagamentos completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Pagamentos pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Pagamentos falhados
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Pagamentos por método
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    // === MÉTODOS DE CONVENIÊNCIA ===

    /**
     * Verificar se o pagamento foi confirmado
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'completed' && !is_null($this->confirmed_at);
    }

    /**
     * Verificar se o pagamento está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verificar se o pagamento falhou
     */
    public function hasFailed(): bool
    {
        return in_array($this->status, ['failed', 'cancelled']);
    }

    /**
     * Obter texto do status em português
     */
    public function getStatusText(): string
    {
        $statusTexts = [
            'pending' => 'Pendente',
            'processing' => 'Processando',
            'completed' => 'Confirmado',
            'failed' => 'Falhou',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado'
        ];

        return $statusTexts[$this->status] ?? 'Desconhecido';
    }

    /**
     * Obter cor do status
     */
    public function getStatusColor(): string
    {
        $statusColors = [
            'pending' => '#F59E0B',      // warning
            'processing' => '#3B82F6',   // blue
            'completed' => '#10B981',    // success
            'failed' => '#EF4444',       // error
            'cancelled' => '#6B7280',    // gray
            'refunded' => '#8B5CF6'      // purple
        ];

        return $statusColors[$this->status] ?? '#6B7280';
    }

    /**
     * Obter nome do método de pagamento
     */
    public function getPaymentMethodName(): string
    {
        $methodNames = [
            'cash' => 'Dinheiro',
            'mpesa' => 'M-Pesa',
            'emola' => 'e-Mola',
            'card' => 'Cartão'
        ];

        return $methodNames[$this->payment_method] ?? 'Desconhecido';
    }

    /**
     * Calcular valor líquido (amount - fee_amount)
     */
    public function calculateNetAmount(): float
    {
        return $this->amount - ($this->fee_amount ?? 0);
    }

    /**
     * Atualizar valor líquido
     */
    public function updateNetAmount(): bool
    {
        $newNetAmount = $this->calculateNetAmount();

        if ($this->net_amount != $newNetAmount) {
            return $this->update(['net_amount' => $newNetAmount]);
        }

        return true;
    }

    // === ACCESSORS ===

    /**
     * Accessor para valor formatado
     */
    public function getAmountFormattedAttribute(): string
    {
        return number_format($this->amount, 2, ',', '.') . ' MT';
    }

    /**
     * Accessor para taxa formatada
     */
    public function getFeeAmountFormattedAttribute(): string
    {
        return number_format($this->fee_amount ?? 0, 2, ',', '.') . ' MT';
    }

    /**
     * Accessor para valor líquido formatado
     */
    public function getNetAmountFormattedAttribute(): string
    {
        return number_format($this->net_amount ?? $this->calculateNetAmount(), 2, ',', '.') . ' MT';
    }
}
