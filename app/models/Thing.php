<?php

class Thing extends Eloquent {

    protected $guarded = array();

    /**
     * Validation rules.
     *
     * @var array
     */
    public static $rules = array(
        'name' => 'required|unique:things,name,:id:'
    );

    /**
     * Validation error messages.
     *
     * @var array
     */
    public static $messages = array(
        'name.required' => 'Navn må fylles ut',
        'name.unique' => 'Tingen finnes allerede'
    );

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

        $v = Validator::make($this->attributes, $rules, $messages);

        if ($v->fails()) {
            $this->errors = $v->messages();
            return false;
        }

        $this->errors = null;
        return true;
    }

    public function documents()
    {
        return $this->hasMany('Document');
    }


    public function activeLoans()
    {
        $loans = array();
        foreach ($this->documents as $doc) {
            foreach ($doc->loans as $loan) {
                $loans[] = $loan;
            }
        }
        return $loans;
    }

    public function allLoans()
    {
        $loans = array();
        foreach ($this->documents as $doc) {
            foreach ($doc->allLoans as $loan) {
                $loans[] = $loan;
            }
        }
        return $loans;
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
            Log::info('Opprettet ny ting: ' . $this->name);
        } else {
            Log::info('Oppdaterte tingen: ' . $this->name);
        }
        parent::save($options);
        return true;
    }

}