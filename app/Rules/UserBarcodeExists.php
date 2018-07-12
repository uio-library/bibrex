<?php

namespace App\Rules;

use App\User;
use Illuminate\Contracts\Validation\Rule;

class UserBarcodeExists implements Rule
{
    protected $barcode;

    /**
     * Create a new rule instance.
     *
     * @param User $user
     */
    public function __construct($barcode)
    {
        $this->barcode = $barcode;
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
    }
}
