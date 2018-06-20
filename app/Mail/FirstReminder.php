<?php

namespace App\Mail;

use App\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Loan;

class FirstReminder extends Mailable
{
    use Queueable, SerializesModels;

    protected $subjectTpl = [
        'eng' => '{thing} must be returned',
        'nob' => '{thing} må leveres tilbake',
        'nno' => '{thing} må leveres tilbake',  // @TODO
    ];

    protected $reminder;

    /**
     * Create a new message instance.
     *
     * @param Loan $loan
     */
    public function __construct(Loan $loan)
    {
        $thing = $loan->item->thing;
        $sender = $loan->library;
        $lang = $loan->user->lang;

        switch ($lang) {
            case 'eng':
                $subject = mb_ucfirst(str_replace('{thing}', $thing->email_name_definite_eng, $this->subjectTpl[$lang]));
                $view = 'emails.first_reminder.eng_html';
                $view_args = [
                    'indefinite' => $thing->email_name_eng,
                    'definite' => $thing->email_name_definite_eng,
                    'relativeTime' => $loan->relativeCreationTime(),
                    'library' => \Auth::user()->name_eng,
                ];
                $sender_name = $sender->name_eng;
                break;
            default:
                $subject = mb_ucfirst(str_replace('{thing}', $thing->email_name_definite_nob, $this->subjectTpl[$lang]));
                $view = 'emails.first_reminder.nob_html';
                $view_args = [
                    'indefinite' => $thing->email_name_nob,
                    'definite' => $thing->email_name_definite_nob,
                    'relativeTime' => $loan->relativeCreationTime(),
                    'library' => \Auth::user()->name,
                ];
                $sender_name = $sender->name;
                break;
        }

        $reminder = new Reminder();
        $reminder->loan_id = $loan->id;
        $reminder->medium = 'email';
        $reminder->type = 'FirstReminder';
        $reminder->subject = $subject;
        $reminder->sender_name = $sender_name;
        $reminder->sender_mail = $sender->email;
        $reminder->receiver_name = $loan->user->firstname . ' ' . $loan->user->lastname;
        $reminder->receiver_mail = $loan->user->email;
        $reminder->body = \View::make($view, $view_args);

        $this->reminder = $reminder;
    }

    public function getReminder()
    {
        return $this->reminder;
    }

    public function save()
    {
        $this->reminder->save();
        return $this;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from($this->reminder->sender_mail, $this->reminder->sender_name)
            ->to($this->reminder->receiver_mail, $this->reminder->receiver_name)
            ->subject($this->reminder->subject)
            ->text('emails.generic')
            ->with([
                'content' => $this->reminder->body,
            ]);
    }
}
