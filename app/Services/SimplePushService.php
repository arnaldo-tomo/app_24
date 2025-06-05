<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PushService
{
    public function sendOrderUpdate($user, $orderId, $status)
    {
        if (!$user->push_token) return;

        $messages = [
            'pending' => '⏰ Aguardando pagamento',
            'confirmed' => '✅ Pedido confirmado!',
            'preparing' => '👨‍🍳 Preparando seu pedido',
            'on_way' => '🚗 Pedido a caminho!',
            'delivered' => '🎉 Pedido entregue!',
            'cancelled' => '❌ Pedido cancelado'
        ];

        Http::post('https://exp.host/--/api/v2/push/send', [
            'to' => $user->push_token,
            'title' => "Pedido #$orderId",
            'body' => $messages[$status] ?? 'Status atualizado',
            'sound' => 'default'
        ]);
    }
}