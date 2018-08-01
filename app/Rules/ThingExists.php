<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ThingExists implements Rule
{
    protected $item;

    /**
     * Create a new rule instance.
     *
     * @param $item
     */
    public function __construct($item = null)
    {
        $this->item = $item;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !is_null($this->item);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Tingen ble ikke funnet! Kanskje du ser etter tingen i seg selv?';
    }
}
