<?php
// database/seeders/SettingSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'FoodDelivery',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Nome da aplicação',
                'is_public' => true,
            ],
            [
                'key' => 'app_description',
                'value' => 'Sistema de delivery de comida',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Descrição da aplicação',
                'is_public' => true,
            ],
            [
                'key' => 'contact_email',
                'value' => 'contato@fooddelivery.mz',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Email de contato',
                'is_public' => true,
            ],
            [
                'key' => 'contact_phone',
                'value' => '+258 84 123 4567',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Telefone de contato',
                'is_public' => true,
            ],

            // Delivery Settings
            [
                'key' => 'default_delivery_fee',
                'value' => '50.00',
                'type' => 'number',
                'group' => 'delivery',
                'description' => 'Taxa de entrega padrão (MT)',
                'is_public' => false,
            ],
            [
                'key' => 'free_delivery_minimum',
                'value' => '200.00',
                'type' => 'number',
                'group' => 'delivery',
                'description' => 'Valor mínimo para frete grátis (MT)',
                'is_public' => true,
            ],
            [
                'key' => 'max_delivery_distance',
                'value' => '10',
                'type' => 'number',
                'group' => 'delivery',
                'description' => 'Distância máxima de entrega (km)',
                'is_public' => false,
            ],
            [
                'key' => 'estimated_delivery_time',
                'value' => '45',
                'type' => 'number',
                'group' => 'delivery',
                'description' => 'Tempo estimado de entrega padrão (minutos)',
                'is_public' => true,
            ],

            // Payment Settings
            [
                'key' => 'commission_percentage',
                'value' => '15.00',
                'type' => 'number',
                'group' => 'payment',
                'description' => 'Comissão da plataforma (%)',
                'is_public' => false,
            ],
            [
                'key' => 'min_withdrawal_amount',
                'value' => '100.00',
                'type' => 'number',
                'group' => 'payment',
                'description' => 'Valor mínimo para saque (MT)',
                'is_public' => false,
            ],
            [
                'key' => 'payment_processing_days',
                'value' => '7',
                'type' => 'number',
                'group' => 'payment',
                'description' => 'Dias para processamento de pagamento',
                'is_public' => false,
            ],

            // Notification Settings
            [
                'key' => 'email_notifications_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notification',
                'description' => 'Ativar notificações por email',
                'is_public' => false,
            ],
            [
                'key' => 'sms_notifications_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notification',
                'description' => 'Ativar notificações por SMS',
                'is_public' => false,
            ],
            [
                'key' => 'push_notifications_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notification',
                'description' => 'Ativar notificações push',
                'is_public' => false,
            ],

            // Appearance Settings
            [
                'key' => 'primary_color',
                'value' => '#ea580c',
                'type' => 'string',
                'group' => 'appearance',
                'description' => 'Cor primária da aplicação',
                'is_public' => true,
            ],
            [
                'key' => 'logo_url',
                'value' => '',
                'type' => 'file',
                'group' => 'appearance',
                'description' => 'URL do logo da aplicação',
                'is_public' => true,
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Modo de manutenção',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
