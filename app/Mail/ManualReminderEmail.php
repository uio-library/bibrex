<?php

namespace App\Mail;

use App\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ManualReminderEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param Loan $loan
     * @param $subject
     * @param $body
     */
    public function __construct(Loan $loan, $subject, $body)
    {
        $sender = $loan->library;
        $lang = $loan->user->lang;

        switch ($lang) {
            case 'eng':
                $sender_name = $sender->name_eng;
                break;
            default:
                $sender_name = $sender->name;
                break;
        }

        $this->data = [
            'sender_name' => $sender_name,
            'sender_mail' => $sender->email,
            'receiver_name' => $loan->user->firstname . ' ' . $loan->user->lastname,
            'receiver_mail' => $loan->user->email,
            'subject' => (string) $subject,
            'body' => (string) $body,
        ];
    }

    public function toArray() {
        return $this->data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from($this->data['sender_mail'], $this->data['sender_name'])
            ->to($this->data['receiver_mail'], $this->data['receiver_name'])
            ->subject($this->data['subject'])
            ->text('emails.generic')
            ->with([
                'content' => $this->data['body'],
            ]);
    }
}
