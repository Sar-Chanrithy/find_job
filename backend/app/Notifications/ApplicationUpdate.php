<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ApplicationUpdate extends Notification
{
    public function __construct(public string $message, public int $applicationId) {}
    public function via(object $notifiable): array { return ['database']; }
    public function toArray(object $notifiable): array
    {
        return ['message' => $this->message, 'application_id' => $this->applicationId];
    }
}
