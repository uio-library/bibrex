<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\MessageBag;

class Library extends Authenticatable
{

    use Notifiable;

    public function ips()
    {
        return $this->hasMany(LibraryIp::class);
    }

    public function settings()
    {
        return $this->hasMany(ThingSettings::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function getOptionsAttribute($value)
    {
        if (is_null($value)) {
            return json_decode('{}', true);
        }
        return json_decode($value, true);
    }

    public function setOptionsAttribute($value)
    {
        $this->attributes['options'] = json_encode($value);
    }

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'libraries';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password');

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifierName()
    {
        return 'email';
    }

    public function getLoansCount()
    {
        return $this->loans()->withTrashed()->count();
    }

    public function getActiveLoansCount()
    {
        return $this->loans()->count();
    }
}
