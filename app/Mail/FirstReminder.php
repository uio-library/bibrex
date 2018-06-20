<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Reminder;

class FirstReminder extends Mailable
{
    use Queueable, SerializesModels;

    protected $reminder;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $sender = $this->reminder->loan->library;
        $rcpt = $this->reminder->loan->user;

        return $this
            ->from($sender->email, $sender->name)
            ->to($rcpt->email, $rcpt->firstname . ' ' . $rcpt->lastname)
            ->subject($this->reminder->subject)
            ->text('emails.generic')
            ->with([
                'content' => $this->reminder->body,
            ]);
    }
}
