<?php

namespace App\Notifications;

use App\Loan;
use App\Mail\ManualReminderEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ManualReminder extends Notification
{
    use Queueable;

    public $loan_id;
    public $email;

    /**
     * Create a new notification instance.
     *
     * @param Loan $loan
     * @param $subject
     * @param $body
     */
    public function __construct(Loan $loan, $subject, $body)
    {
        $this->loan_id = $loan->id;
        $this->email = new ManualReminderEmail($loan, $subject, $body);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', ExtendedDatabaseChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return ManualReminderEmail
     */
    public function toMail($notifiable)
    {
        return $this->email;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'loan_id' => $this->loan_id,
            'email' => $this->email->toArray(),
        ];
    }
}
