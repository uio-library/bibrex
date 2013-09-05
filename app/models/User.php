<?php

class User extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	public static $rules = array(
		'ltid' => 'not_guest_ltid' // this rule is defined in app/start/global.php
	);
	public static $messages = array(
		'not_guest_ltid' => 'Det midlertidige lånekortet skal aldri skannes i Bibrex. Hvis bruker ikke har lånekort skal man istedet oppgi personens navn'
	);

	public function validate()
	{
		$v = Validator::make($this->attributes, static::$rules, static::$messages);

		if ($v->passes()) return true;

		$this->errors = $v->messages();
		return false;
	}

	public function loans()
	{
		return $this->hasMany('Loan');
	}

	public function deliveredLoans()
	{
		return $this->hasMany('Loan')
			->whereNotNull('deleted_at')
			->with('document.thing')
			->withTrashed()
			->orderBy('created_at', 'desc');
	}

	public function name()
	{
		return $this->firstname . ' ' . $this->lastname;
	}

	/**
	 * Mutuator for the ltid field
	 *
	 * @param  string  $value
	 * @return void
	 */
	public function setLtidAttribute($value)
	{
		if (is_null($value)) {
			$this->attributes['ltid'] = null;
		} else {
			$this->attributes['ltid'] = strtolower($value);
		}
	}

	/**
	 * Make a lookupUser request to the NCIP service
	 *
	 * @return UserResponse
	 */
	public function ncipLookup() {
		if ($this->ltid) {
			$ncip = App::make('NcipClient');
			$response = $ncip->lookupUser($this->ltid);
			return $response;
		} else {
			return null;
		}
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
		if (!$this->exists) {
			if ($this->ltid) {
				$response = $this->ncipLookup();
				$this->in_bibsys = $response->exists;
				if ($response->exists) {
					$this['lastname'] = $response->lastName;
					$this['firstname'] = $response->firstName;
					$this['email'] = $response->email;
					$this['phone'] = $response->phone;
					Log::info('Fant [[User:' . $this->ltid . ']] i BIBSYS');
				} else {
					Log::info('Fant ikke [[User:' . $this->ltid . ']] i BIBSYS');
				}
			} else {
				$this->in_bibsys = false;
			}
			Log::info('Opprettet ny bruker i BIBREX');
		} else {
			Log::info('Oppdaterte opplysningene for bruker [[User:' . $this->id . ']].');
		}
		parent::save($options);
		return true;
	}

}
