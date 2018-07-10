<?php

namespace App\Rules;

use App\Item;
use Illuminate\Contracts\Validation\Rule;

class NotOnLoan implements Rule
{
    protected $item;

    /**
     * Create a new rule instance.
     *
     * @param Item $item
     */
    public function __construct(Item $item = null)
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
        if (is_null($this->item)) {
            return true;
        }

        // Always true if the item is a generic representaiton without barcode
        if (is_null($this->item->barcode)) {
            return true;
        }

        // Check if already on loan
        $loan = $this->item->loans()->first();
        return is_null($loan);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Tingen er allerede utlånt.';
    }
}
