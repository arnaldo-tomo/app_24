<?php 
namespace App\Console\Commands;

use App\Models\Notification;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;

class SendPendingNotifications extends Command
{
    protected $signature = 'notifications:send-pending';
    protected $description = 'Enviar notificações pendentes';

    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        parent::__construct();
        $this->pushService = $pushService;
    }

    public function handle()
    {
        $this->info('Enviando notificações pendentes...');

        $pendingNotifications = Notification::with('user')
            ->where('is_sent', false)
            ->whereNull('error_message')
            ->limit(100)
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($pendingNotifications as $notification) {
            try {
                $success = $this->pushService->sendToUser(
                    $notification->user,
                    $notification->title,
                    $notification->body,
                    $notification->data ?? [],
                    $notification->type
                );

                if ($success) {
                    $notification->markAsSent();
                    $sent++;
                } else {
                    $notification->markAsFailed('Falha no envio');
                    $failed++;
                }

            } catch (\Exception $e) {
                $notification->markAsFailed($e->getMessage());
                $failed++;
            }
        }

        $this->info("Enviadas: {$sent}, Falharam: {$failed}");
    }
}
