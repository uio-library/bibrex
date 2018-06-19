<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Reminder;

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

        return $this
            ->from($sender->email, $sender->name)
            ->subject($this->reminder->subject)
            ->view('emails.generic')
            ->with([
                'content' => $this->reminder->body,
            ]);
    }
}
