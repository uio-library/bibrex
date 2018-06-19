<?php

namespace App\Rules;

use App\Item;
use App\Thing;
use Illuminate\Contracts\Validation\Rule;

class ThingExists implements Rule
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
        if (!$value) {
            return false;
        }
        if (Item::where('dokid', '=', $value)->exists()) {
            return true;
        }
        if (Thing::where('name', '=', $value)->exists()) {
//            $doc = $thing->whereNull('dokid')->first();
//            if (is_null($doc)) {
//
//            }
//            return false;

            return true;
        }

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
