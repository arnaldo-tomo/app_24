<?php
namespace App\Listeners;

use App\Services\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Handle the event.
     */
    public function handle($event)
    {
        $order = $event->order;
        $user = $order->customer;

        if (!$user) {
            \Log::warning("Pedido {$order->id} não possui usuário associado");
            return;
        }

        // Enviar notificação baseada no status
        $this->pushService->sendOrderStatusUpdate($user, $order, $order->status);
    }
}
