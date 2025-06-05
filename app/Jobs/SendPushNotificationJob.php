<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $title;
    protected $body;
    protected $data;
    protected $type;

    public function __construct($userId, $title, $body, $data = [], $type = 'general')
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->type = $type;
    }

    public function handle(PushNotificationService $pushService)
    {
        $user = User::find($this->userId);

        if ($user) {
            $pushService->sendToUser(
                $user,
                $this->title,
                $this->body,
                $this->data,
                $this->type
            );
        }
    }
}