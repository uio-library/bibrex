<?php

use Illuminate\Support\MessageBag;
use Scriptotek\Alma\Client as AlmaClient;

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
	 * @return Scriptotek\Ncip\UserResponse
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

	public function almaLookup() {
		if (!$this->ltid) {
			return null;
		}
		$alma = new AlmaClient(Config::get('app.alma_key'), 'eu');
		$users = $alma->users->search('ALL~' . $this->ltid);
		try {
			foreach ($users as $u) {
				break;
			}
		} catch (\ErrorException $e) {
			return null;
		}

		$email = '';
		$phone = '';
		foreach ($u->contact_info->email as $e) {
			if ($e->preferred) $email = $e->email_address;
		}
		foreach ($u->contact_info->phone as $e) {
			if ($e->preferred) $phone = $e->phone_number;
		}
		if (in_array($u->preferred_language->value, ['no', 'nb', 'nn'])) {
			$lang = 'nor';
		} else {
			$lang = 'eng';
		}
		return [
			'first_name' => $u->first_name,
			'last_name' => $u->last_name,
			'phone' => $phone,
			'email' => $email,
			'lang' => $lang,
		];

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
		$isNew = !$this->exists;
		if ($isNew) {
			$this->in_bibsys = false;
			if ($this->ltid) {
				$almaUser = $this->almaLookup();
				if (!is_null($almaUser)) {
					$this->mergeFromUserResponse($almaUser);
					Log::info('Fant User:' . $this->ltid . ' i Alma.');
				} else {
					Log::info('Fant ikke User:' . $this->ltid . ' i Alma.');
				}
			}
		} else {

			// TODO: Hva hvis LTID endres fra et nr. til et annet og brukeren har lån?
			//       Da må lånene overføres

		}
		parent::save($options);
		if ($isNew) {
			Log::info('Opprettet ny bruker: User:' . $this->id . '.');
		} else {
			Log::info('Oppdaterte opplysningene for bruker User:' . $this->id . '.');
		}

		return true;
	}

	/**
	 * Merge in UserResponse data
	 *
	 * @return void
	 */
	public function mergeFromUserResponse($response)
	{
		if (is_null($response)) {
			$this->in_bibsys = false;
			return;
		}
		$this->in_bibsys = true;
		$this->lastname = $response['last_name'];
		$this->firstname = $response['first_name'];
		$this->email = $response['email'];
		$this->phone = $response['phone'];
		$this->lang = $response['lang'];
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

		// if (!empty($ltid) && !empty($user->ltid) && ($ltid != $user->ltid)) {
		// 	$errors->add('ltid_conflict', "Kan ikke flette nytt LTID $ltid med eksisterende $user->ltid.");
		// }

		// if (!empty($ltid) && !empty($this->ltid) && ($ltid != $this->ltid)) {
		// 	$errors->add('ltid_conflict', "Kan ikke flette nytt LTID $ltid med eksisterende $this->ltid.");
		// }

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
