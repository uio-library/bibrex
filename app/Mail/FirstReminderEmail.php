<?php

namespace App\Mail;

use App\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use function Stringy\create as s;

class FirstReminderEmail extends Mailable
{
    use Queueable;

    protected $subjectTpl = [
        'eng' => '{thing} must be returned',
        'nob' => '{thing} må leveres tilbake',
        'nno' => '{thing} må leverast tilbake',
    ];

    protected $data = [];

    /**
     * Create a new message instance.
     * @param Loan $loan
     */
    public function __construct(Loan $loan)
    {
        $thing = $loan->item->thing;
        $sender = $loan->library;
        $lang = $loan->user->lang;

        $subject = s($this->subjectTpl[$lang])
            ->replace('{thing}', $thing->properties->get('name_definite.' . $lang))
            ->upperCaseFirst();
        $view = 'emails.first_reminder.' . $lang;
        $view_args = [
            'indefinite' => $thing->properties->get('name_indefinite.' . $lang),
            'definite' => s($thing->properties->get('name_definite.' . $lang))->upperCaseFirst(),
            'relativeTime' => $loan->relativeCreationTime(),
            'barcode' => $loan->item->barcode,
        ];

        if ($lang == 'eng') {
            $view_args['library'] = $loan->library->name_eng;
            $sender_name = $sender->name_eng;
        } else {
            $view_args['library'] = $loan->library->name;
            $sender_name = $sender->name;
        }

        $this->data = [
            'sender_name' => $sender_name,
            'sender_mail' => $sender->email,
            'receiver_name' => $loan->user->firstname . ' ' . $loan->user->lastname,
            'receiver_mail' => $loan->user->email,
            'subject' => (string) $subject,
            'body' => (string) \View::make($view, $view_args),
        ];
    }

    public function toArray()
    {
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
