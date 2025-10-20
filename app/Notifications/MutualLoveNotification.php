<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MutualLoveNotification extends Notification
{
    use Queueable;

    protected $fromUser;

    public function __construct($fromUser)
    {
        $this->fromUser = $fromUser;
    }

    public function via($notifiable): array
    {
        return ['database']; // DB通知（bellアイコン用）。MailやBroadcastも追加可能
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => "{$this->fromUser->name} さんと両想いになりました！ 💖",
            'user_id' => $this->fromUser->id,
        ];
    }
}
