<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LibraryIp extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'library_ips';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array('library_id', 'ip');

    public function library()
    {
        return $this->belongsTo(Library::class);
    }

    /**
     * Array of user-editable attributes (excluding machine-generated stuff)
     *
     * @static array
     */
    public static $editableAttributes = array('ip');

    public static $rules = array(
        'ip' => 'required|ip|unique:library_ips,ip,:id:'
    );

    /**
     * Validation error messages.
     *
     * @static array
     */
    public static $messages = array(
        'ip.required' => 'Adressen er tom',
        'ip.unique' => 'Adressen må være unik',
        'ip.ip' => 'Ugyldig ip-adresse'
    );

    /**
     * Process validation rules.
     *
     * @param  array  $rules
     * @return array  $rules
     */
    protected function processRules(array $rules)
    {
        $id = $this->getKey();
        array_walk($rules, function (&$item) use ($id) {
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

        $v = \Validator::make($this->attributes, $rules, $messages);

        if ($v->fails()) {
            $this->errors = $v->messages();
            return false;
        }

        return true;
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
            \Log::info('Opprettet ny ip: ' . $this->name);
        } else {
            \Log::info('Oppdaterte ip-en: ' . $this->name);
        }*/
        parent::save($options);
        return true;
    }
}
