<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\MessageBag;

class Library extends Authenticatable {

    use Notifiable;

    /**
	 * Array of user-editable attributes (excluding machine-generated stuff)
	 *
	 * @static array
	 */
	public static $editableAttributes = array('name', 'email', 'guest_ltid');

	public function ips()
	{
		return $this->hasMany(LibraryIp::class);
	}

	public function loans()
	{
		return $this->hasMany(Loan::class);
	}

    /**
     * The things activated at the library.
     */
    public function things()
    {
        return $this->belongsToMany('App\Things');
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
	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

	public function getLoansCount()
	{
		return $this->loans()->withTrashed()->count();
	}

	public function getActiveLoansCount()
	{
		return $this->loans()->count();
	}

	/**
	 * Get the token value for the "remember me" session.
	 *
	 * @return string
	 */
	public function getRememberToken()
	{
		return $this->remember_token;
	}

	/**
	 * Set the token value for the "remember me" session.
	 *
	 * @param  string  $value
	 * @return void
	 */
	public function setRememberToken($value)
	{
		$this->remember_token = $value;
	}

	/**
	 * Get the column name for the "remember me" token.
	 *
	 * @return string
	 */
	public function getRememberTokenName()
	{
		return 'remember_token';
	}

	/**
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail()
	{
		return $this->email;
	}

}
