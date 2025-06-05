<?php
// database/seeders/PaymentMethodSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'PIX',
                'slug' => 'pix',
                'description' => 'Pagamento instantâneo via PIX',
                'icon' => 'fas fa-qrcode',
                'type' => 'pix',
                'is_active' => true,
                'fee_percentage' => 0,
                'fee_fixed' => 0,
                'sort_order' => 1,
            ],
            [
                'name' => 'Cartão de Crédito',
                'slug' => 'credit-card',
                'description' => 'Pagamento com cartão de crédito',
                'icon' => 'fas fa-credit-card',
                'type' => 'card',
                'is_active' => true,
                'fee_percentage' => 3.99,
                'fee_fixed' => 0.39,
                'sort_order' => 2,
            ],
            [
                'name' => 'Cartão de Débito',
                'slug' => 'debit-card',
                'description' => 'Pagamento com cartão de débito',
                'icon' => 'fas fa-credit-card',
                'type' => 'card',
                'is_active' => true,
                'fee_percentage' => 1.99,
                'fee_fixed' => 0.39,
                'sort_order' => 3,
            ],
            [
                'name' => 'Dinheiro',
                'slug' => 'cash',
                'description' => 'Pagamento em dinheiro na entrega',
                'icon' => 'fas fa-money-bill-wave',
                'type' => 'cash',
                'is_active' => true,
                'fee_percentage' => 0,
                'fee_fixed' => 0,
                'sort_order' => 4,
            ],
            [
                'name' => 'M-Pesa',
                'slug' => 'mpesa',
                'description' => 'Pagamento via M-Pesa',
                'icon' => 'fas fa-mobile-alt',
                'type' => 'digital_wallet',
                'is_active' => true,
                'fee_percentage' => 1.5,
                'fee_fixed' => 0,
                'sort_order' => 5,
            ],
            [
                'name' => 'Transferência Bancária',
                'slug' => 'bank-transfer',
                'description' => 'Pagamento via transferência bancária',
                'icon' => 'fas fa-university',
                'type' => 'bank_transfer',
                'is_active' => false,
                'fee_percentage' => 0,
                'fee_fixed' => 5.00,
                'sort_order' => 6,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }
    }
}
