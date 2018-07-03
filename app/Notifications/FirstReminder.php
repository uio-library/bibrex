<?php

namespace App\Notifications;

use App\Loan;
use App\Mail\FirstReminderEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FirstReminder extends Notification
{
    use Queueable;

    protected $subjectTpl = [
        'eng' => '{thing} must be returned',
        'nob' => '{thing} må leveres tilbake',
        'nno' => '{thing} må leveres tilbake',  // @TODO
    ];

    public $loan_id;
    public $email;

    /**
     * Create a new notification instance.
     *
     * @param Loan $loan
     */
    public function __construct(Loan $loan)
    {
        $this->loan_id = $loan->id;
        $this->email = new FirstReminderEmail($loan);
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
     * @return FirstReminderEmail
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
