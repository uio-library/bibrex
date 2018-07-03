<?php

namespace App\Notifications;

use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification;

class ExtendedDatabaseChannel extends DatabaseChannel
{
    /**
     * Build an array payload for the DatabaseNotification Model.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array
     */
    protected function buildPayload($notifiable, Notification $notification)
    {
        $data = $this->getData($notifiable, $notification);
        return [
            'id' => $notification->id,
            'type' => get_class($notification),
            'read_at' => null,

            'loan_id' => array_get($data, 'loan_id'),
            'data' => [
                'email' => array_get($data, 'email'),
            ],
        ];
    }
}
