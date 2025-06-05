<?php
// app/Models/PaymentMethod.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'type', 'is_active',
        'configuration', 'sort_order', 'fee_percentage', 'fee_fixed'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'configuration' => 'array',
            'fee_percentage' => 'decimal:2',
            'fee_fixed' => 'decimal:2',
        ];
    }

    // Types: card, pix, cash, bank_transfer, digital_wallet
    const TYPES = [
        'card' => 'Cartão de Crédito/Débito',
        'pix' => 'PIX',
        'cash' => 'Dinheiro',
        'bank_transfer' => 'Transferência Bancária',
        'digital_wallet' => 'Carteira Digital'
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'payment_method', 'slug');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTypeLabel()
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function calculateFee($amount)
    {
        $fee = 0;

        if ($this->fee_percentage > 0) {
            $fee += ($amount * $this->fee_percentage / 100);
        }

        if ($this->fee_fixed > 0) {
            $fee += $this->fee_fixed;
        }

        return round($fee, 2);
    }
}