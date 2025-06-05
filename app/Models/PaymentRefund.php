<?php
// app/Models/PaymentRefund.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'refund_id', 'amount', 'reason', 'status', 'refunded_at',
        'failure_reason', 'metadata', 'payment_id', 'processed_by'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refunded_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    const STATUSES = [
        'pending' => 'Pendente',
        'processing' => 'Processando',
        'completed' => 'Concluído',
        'failed' => 'Falhado',
        'cancelled' => 'Cancelado'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($refund) {
            if (empty($refund->refund_id)) {
                $refund->refund_id = 'REF-' . strtoupper(uniqid());
            }
        });
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getStatusLabel()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }
}
