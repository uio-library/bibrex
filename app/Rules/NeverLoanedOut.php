<?php

namespace App\Rules;

use App\Item;
use Illuminate\Contracts\Validation\Rule;
use function Stringy\create as s;

class NeverLoanedOut implements Rule
{
    protected $item;

    /**
     * Create a new rule instance.
     *
     * @param Item $item
     */
    public function __construct(Item $item)
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
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return sprintf(
            '%s har egentlig aldri vært utlånt så vidt Bibrex kan se.',
            s($this->item->thing->properties->get('name_definite.nob'))->upperCaseFirst()
        );
    }
}
