<?php

class User extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

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
	 * Save the model to the database.
	 *
	 * @param  array  $options
	 * @return bool
	 */
	public function save(array $options = array())
	{
		if ($this->ltid) {
			$ncip = new Ncip();
			$response = $ncip->lookupUser($this->ltid);
			$this->in_bibsys = $response['exists'];
			if ($response['exists']) {
				$this['lastname'] = $response['lastname'];
				$this['firstname'] = $response['firstname'];
				$this['email'] = $response['email'];
				$this['phone'] = $response['phone'];
			}
		} else {
			$this->in_bibsys = false;
		}

		parent::save($options);
	}

}
