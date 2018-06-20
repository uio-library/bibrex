<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LibraryThing extends Pivot
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'require_item' => 'boolean',
        'send_reminders' => 'boolean',
    ];
}
