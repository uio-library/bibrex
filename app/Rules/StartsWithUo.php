<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StartsWithUo implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        if (!isset($this->data['thing']) || ($this->data['thing'] != '1')) {  // Ikke bibsys-dok:
            return true;
        }
        if (!preg_match('/[0-9]/', $value)) { // Inneholder ikke tall: antakelig et navn
            return true;
        }
        return substr($value, 0, 2) == 'uo';
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Card number must start with "uo".';
    }
}
