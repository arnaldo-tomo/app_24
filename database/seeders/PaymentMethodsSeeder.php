<?php

// database/seeders/PaymentMethodsSeeder.php php artisan db:seed --class=PaymentMethodsSeeder

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodsSeeder extends Seeder
{
    public function run()
    {
        $paymentMethods = [
            [
                'name' => 'M-Pesa',
                'slug' => 'mpesa',
                'description' => 'Pagamento via M-Pesa (Vodacom)',
                'icon' => 'phone-portrait-outline',
                'type' => 'digital_wallet',
                'is_active' => true,
                'sort_order' => 1,
                'fee_percentage' => 0.00, // Sem taxa para o cliente
                'fee_fixed' => 0.00,
                'configuration' => [
                    'requires_phone' => true,
                    'min_amount' => 1,
                    'max_amount' => 100000,
                    'supported_operators' => ['vodacom'],
                    'currency' => 'MZN'
                ]
            ],
            [
                'name' => 'eMola',
                'slug' => 'emola',
                'description' => 'Pagamento via eMola (BCI/Millennium)',
                'icon' => 'card-outline',
                'type' => 'digital_wallet',
                'is_active' => true,
                'sort_order' => 2,
                'fee_percentage' => 0.00,
                'fee_fixed' => 0.00,
                'configuration' => [
                    'requires_phone' => true,
                    'min_amount' => 1,
                    'max_amount' => 50000,
                    'supported_operators' => ['bci', 'millennium'],
                    'currency' => 'MZN'
                ]
            ],
            [
                'name' => 'Dinheiro',
                'slug' => 'cash',
                'description' => 'Pagamento em dinheiro na entrega',
                'icon' => 'cash-outline',
                'type' => 'cash',
                'is_active' => true,
                'sort_order' => 3,
                'fee_percentage' => 0.00,
                'fee_fixed' => 0.00,
                'configuration' => [
                    'requires_phone' => false,
                    'requires_change' => true,
                    'min_amount' => 1,
                    'max_amount' => 10000, // Limite para segurança
                    'currency' => 'MZN'
                ]
            ],
            // Métodos futuros (inativos por agora)
            [
                'name' => 'Cartão de Crédito/Débito',
                'slug' => 'card',
                'description' => 'Pagamento com cartão de crédito ou débito',
                'icon' => 'card',
                'type' => 'card',
                'is_active' => false, // Desativado por enquanto
                'sort_order' => 4,
                'fee_percentage' => 3.5, // Taxa típica de cartão
                'fee_fixed' => 0.00,
                'configuration' => [
                    'requires_card_data' => true,
                    'supports_installments' => true,
                    'max_installments' => 12,
                    'min_amount' => 10,
                    'currency' => 'MZN'
                ]
            ],
            [
                'name' => 'Transferência Bancária',
                'slug' => 'bank_transfer',
                'description' => 'Transferência bancária direta',
                'icon' => 'business-outline',
                'type' => 'bank_transfer',
                'is_active' => false,
                'sort_order' => 5,
                'fee_percentage' => 0.00,
                'fee_fixed' => 15.00, // Taxa fixa típica
                'configuration' => [
                    'requires_bank_details' => true,
                    'processing_time' => '1-2 business days',
                    'min_amount' => 100,
                    'currency' => 'MZN'
                ]
            ]
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['slug' => $method['slug']],
                $method
            );
        }

        $this->command->info('Payment methods seeded successfully!');
    }
}

