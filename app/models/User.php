<?php

use Illuminate\Support\MessageBag;
use Danmichaelo\Ncip\UserResponse;
use Danmichaelo\Ncip\InvalidNcipResponseException;
class User extends Eloquent {

	/**
	 * Array of user-editable attributes (excluding machine-generated stuff)
	 *
	 * @static array
	 */
	public static $editableAttributes = array('ltid', 'lastname', 'firstname', 'phone', 'email', 'lang');

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
	 * @return Danmichaelo\Ncip\UserResponse
	 */
	public function ncipLookup() {
		if ($this->ltid) {
			$ncip = App::make('ncip.client');
			try {
				Log::info('[NCIP] Henter brukerinfo for ' . $this->ltid);
				return $ncip->lookupUser($this->ltid);
			} catch (InvalidNcipResponseException $e) {
				return null;
			}
		}
		return null;
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
			$this->in_bibsys = false;
			if ($this->ltid) {
				$response = $this->ncipLookup();
				if (!is_null($response)) {
					$this->in_bibsys = $response->exists;
					if ($response->exists) {
						$this->mergeFromUserResponse($response);
						Log::info('Fant [[User:' . $this->ltid . ']] i BIBSYS');
					} else {
						Log::info('Fant ikke [[User:' . $this->ltid . ']] i BIBSYS');
					}
				}
			}
			Log::info('Opprettet ny bruker i BIBREX');
		} else {
			Log::info('Oppdaterte opplysningene for bruker [[User:' . $this->id . ']].');

			// TODO: Hva hvis LTID endres fra et nr. til et annet og brukeren har lån?
			//       Da må lånene overføres

		}
		parent::save($options);
		return true;
	}

	/**
	 * Merge in NCIP UserResponse data
	 *
	 * @param  Danmichaelo\Ncip\UserResponse  $response
	 * @return void
	 */
	public function mergeFromUserResponse(UserResponse $response)
	{
		$this->lastname = $response->lastName;
		$this->firstname = $response->firstName;
		$this->email = $response->email;
		$this->phone = $response->phone;
		//$this->lang = $response->lang;  // WAIT: Seems like BIBSYS currently always returns "eng"
	}

	protected function mergeAttribute($key, User $user)
	{
		return strlen($user->$key) > strlen($this->$key) ? $user->$key : $this->$key;
	}

	/**
	 * Get data for merging $user into the current user. The returned
	 * array can be passed directly to mergeWith or presented to a user
	 * for review first.
	 *
	 * @param  User  $user
	 * @return array
	 */
	public function getMergeData($user)
	{
		$merged = array();
		foreach (static::$editableAttributes as $attr) {
			$merged[$attr] = $this->mergeAttribute($attr, $user);
		}
		return $merged;
	}

	/**
	 * Merge in another user $user
	 *
	 * @param  User  $user
	 * @param  array  $data  An array of merged attributes (optional)
	 * @return Illuminate\Support\MessageBag
	 */
	public function merge(User $user, array $data = null)
	{

		if (is_null($data)) {
			$data = $this->getMergeData($user);
		}

		// Validate
		$errors = new MessageBag();
		$ltid = $data['ltid'];
		if (!empty($ltid) && !empty($user->ltid) && ($ltid != $user->ltid)) {
			$errors->add('ltid_conflict', "Kan ikke flette nytt LTID $ltid med eksisterende $user->ltid.");
		}

		if (!empty($ltid) && !empty($this->ltid) && ($ltid != $this->ltid)) {
			$errors->add('ltid_conflict', "Kan ikke flette nytt LTID $ltid med eksisterende $this->ltid.");
		}

		if ($errors->count() > 0) {
			return $errors;
		}

		Log::info('Fletter bruker ' . $user->id . ' inn i bruker ' . $this->id);

		foreach ($user->loans as $loan) {
			$loan->user_id = $this->id;
			$loan->save();
			Log::info('Lån ' . $loan->id . ' flyttet fra bruker ' . $user->id . ' til ' . $this->id);
			if (!$loan->as_guest) {

			}
		}

		// Delete other user first to avoid database integrity conflicts
		$user->delete();

		// Update properties of the current user with merge data
		foreach ($data as $key => $val) {
			$this->$key = $val;
		}

		$this->save();

		return null;
	}

}
