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

        if ($this->options['guestcard_for_nonworking_cards'] || $this->options['guestcard_for_cardless_loans']) {
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

}
