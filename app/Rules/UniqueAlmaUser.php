<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueAlmaUser implements Rule
{
    protected $query;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($query)
    {
        $this->query = $query;
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
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Fant mer enn én bruker i Alma. Prøv å velge en bruker fra nedtrekksmenyen.';
    }
}
