<?php
// app/Models/Payment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 'external_id', 'amount', 'fee_amount', 'net_amount',
        'status', 'payment_date', 'description', 'metadata', 'failure_reason',
        'order_id', 'payment_method_id', 'user_id'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'payment_date' => 'datetime',
            'metadata' => 'array',
        ];
    }

    const STATUSES = [
        'pending' => 'Pendente',
        'processing' => 'Processando',
        'completed' => 'Concluído',
        'failed' => 'Falhado',
        'cancelled' => 'Cancelado',
        'refunded' => 'Estornado',
        'partially_refunded' => 'Estornado Parcialmente'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->transaction_id)) {
                $payment->transaction_id = 'TXN-' . strtoupper(uniqid());
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function refunds()
    {
        return $this->hasMany(PaymentRefund::class);
    }

    // Status helpers
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isRefunded()
    {
        return in_array($this->status, ['refunded', 'partially_refunded']);
    }

    public function canBeRefunded()
    {
        return $this->isCompleted() && $this->refunds()->sum('amount') < $this->amount;
    }

    public function getStatusLabel()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getRefundedAmount()
    {
        return $this->refunds()->sum('amount');
    }

    public function getRemainingRefundableAmount()
    {
        return $this->amount - $this->getRefundedAmount();
    }
}