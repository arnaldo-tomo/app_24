<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'quantity',
        'unit_price',
        'total_price',
        'customizations',
        'special_instructions'
    ];

    protected $casts = [
        'customizations' => 'array',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer'
    ];

    // === RELACIONAMENTOS ===

    /**
     * Pedido ao qual este item pertence
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Item do menu
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    // === ACCESSORS ===

    /**
     * Nome do item (através do menu item)
     */
    public function getNameAttribute(): string
    {
        return $this->menuItem?->name ?? 'Item não encontrado';
    }

    /**
     * Descrição do item (através do menu item)
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->menuItem?->description;
    }

    /**
     * Imagem do item (através do menu item)
     */
    public function getImageAttribute(): ?string
    {
        return $this->menuItem?->image;
    }

    /**
     * Total formatado
     */
    public function getTotalFormattedAttribute(): string
    {
        return number_format($this->total_price, 2, ',', '.') . ' MT';
    }

    /**
     * Preço unitário formatado
     */
    public function getUnitPriceFormattedAttribute(): string
    {
        return number_format($this->unit_price, 2, ',', '.') . ' MT';
    }

    // === MÉTODOS DE CONVENIÊNCIA ===

    /**
     * Calcular total baseado na quantidade e preço unitário
     */
    public function calculateTotal(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Verificar se o total está correto
     */
    public function isValidTotal(): bool
    {
        return abs($this->total_price - $this->calculateTotal()) < 0.01;
    }

    /**
     * Atualizar total baseado na quantidade
     */
    public function updateTotal(): bool
    {
        $newTotal = $this->calculateTotal();

        if ($this->total_price != $newTotal) {
            return $this->update(['total_price' => $newTotal]);
        }

        return true;
    }
}
