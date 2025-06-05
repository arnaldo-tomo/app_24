<?php
namespace App\Observers;

use App\Models\Order;
use App\Services\PushNotificationService;

class OrderObserver
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order)
    {
        // Verificar se o status mudou
        if ($order->isDirty('status')) {
            $user = $order->customer;

            if ($user) {
                $this->pushService->sendOrderStatusUpdate($user, $order, $order->status);
            }
        }

        // Verificar se o pagamento foi confirmado
        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            $user = $order->customer;

            if ($user) {
                $this->pushService->sendPaymentConfirmed($user, $order);
            }
        }
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order)
    {
        // Opcional: enviar notificação quando pedido é criado
        $user = $order->customer;

        if ($user) {
            // Implementar se necessário
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order)
    {
        // Implementar se necessário
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order)
    {
        // Implementar se necessário
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order)
    {
        // Implementar se necessário
    }

} // ← CERTIFIQUE-SE QUE A CHAVE ESTÁ FECHADA CORRETAMENTE
