<?php

use Illuminate\Support\MessageBag;
use Illuminate\Auth\UserInterface;

class Library extends Eloquent implements UserInterface {

	/**
	 * Array of user-editable attributes (excluding machine-generated stuff)
	 *
	 * @static array
	 */
	public static $editableAttributes = array('name', 'email', 'guest_ltid');

	public static $rules = array(
		'name' => 'required|unique:libraries,name,:id:',
		'email' => 'required|email|unique:libraries,email,:id:',
		'guest_ltid' => 'regex:/^[0-9a-zA-Z]{10}$/',
	);

	/**
	 * Validation error messages.
	 *
	 * @static array
	 */
	public static $messages = array(
		'name.required' => 'Navn må fylles ut',
		'name.unique' => 'Navn må være unikt',
		'email.required' => 'Epost må fylles ut',
		'email.unique' => 'Epost må være unik',
		'email.email' => 'Epost må være en gyldig epostadresse',
		'guest_ltid.regex' => 'LTID må være et gyldig LTID',
	);

	public function ips()
	{
		return $this->hasMany('LibraryIp');
	}

	public function loans()
	{
		return $this->hasMany('Loan');
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
	 * Validation errors.
	 *
	 * @var Illuminate\Support\MessageBag
	 */
	public $errors;

	/**
	 * Process validation rules.
	 *
	 * @param  array  $rules
	 * @return array  $rules
	 */
	protected function processRules(array $rules)
	{
		$id = $this->getKey();
		array_walk($rules, function(&$item) use ($id)
		{
			// Replace placeholders
			$item = stripos($item, ':id:') !== false ? str_ireplace(':id:', $id, $item) : $item;
		});

		return $rules;
	}

	/**
	 * Validate the model's attributes.
	 *
	 * @param  array  $rules
	 * @param  array  $messages
	 * @return bool
	 */
	public function validate(array $rules = array(), array $messages = array())
	{
		$rules = $this->processRules($rules ?: static::$rules);
		$messages = $this->processRules($messages ?: static::$messages);

		if (array_get($this->options, 'guestcard_for_nonworking_cards', false) || array_get($this->options, 'guestcard_for_cardless_loans', false)) {
			$rules['guest_ltid'] .= '|required';
			$messages['guest_ltid.required'] = 'Du har valgt å aktivere bruk av gjestekort, men har ikke angitt hvilket kort.';
		}

		$v = Validator::make($this->attributes, $rules, $messages);

		if ($v->fails()) {
			$this->errors = $v->messages();
			return false;
		}

		$this->errors = null;
		return true;
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

	/**
	 * Save the model to the database.
	 *
	 * @param  array  $options
	 * @return bool
	 */
	public function save(array $options = array())
	{
		if (!$this->validate()) {
			return false;
		}
		/*if (!$this->exists) {
			Log::info('Opprettet ny ting: ' . $this->name);
		} else {
			Log::info('Oppdaterte tingen: ' . $this->name);
		}*/
		parent::save($options);
		return true;
	}

	/**
	 * Sets a new password. Note that it does *not store the model*.
	 *
	 * @param  string    $password
	 * @param  string    $passwordRepeated
	 * @return bool
	 */
	public function setPassword($password, $passwordRepeated)
	{
		$errors = new MessageBag;
		if (mb_strlen($password) < 8) {
			$errors->add('pwd_tooshort', "Passordet er for kort (kortere enn 8 tegn).");
		}

		if ($password != $passwordRepeated) {
			$errors->add('pwd_unequal', "Du gjentok ikke passordet likt.");
		}

		if ($errors->count() > 0) {
			$this->errors = $errors;
			return false;
		}

		$this->password = Hash::make($password);
		return true;
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
