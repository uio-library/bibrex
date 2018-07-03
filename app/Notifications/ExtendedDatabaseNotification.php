<?php

namespace App\Notifications;

use App\Loan;
use Illuminate\Notifications\DatabaseNotification;

class ExtendedDatabaseNotification extends DatabaseNotification
{
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['url'];

    public function getUrlAttribute() {
        return action('NotificationsController@show', $this->id);
    }

    public function humanReadableType()
    {
        switch ($this->type) {
            case ManualReminder::class:
                return 'Manuell påminnelse';
            case FirstReminder::class:
                return 'Automatisk påminnelse';
            default:
                return 'Påminnelse';
        }
    }
}