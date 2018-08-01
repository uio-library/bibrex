<?php

namespace App\Rules;

use App\Item;
use Illuminate\Contracts\Validation\Rule;
use Scriptotek\Alma\Bibs\Item as AlmaItem;
use Scriptotek\Alma\Client as AlmaClient;

class NotOnLoan implements Rule
{
    protected $alma;
    protected $item;
    protected $what;

    /**
     * Create a new rule instance.
     *
     * @param AlmaClient $alma
     * @param $item
     */
    public function __construct(AlmaClient $alma, $item = null)
    {
        $this->alma = $alma;
        $this->item = $item;
        $this->msg = 'Tingen er allerede utlånt i Bibrex.';
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

        if (is_a($this->item, AlmaItem::class)) {
            $almaLoan = $this->item->loan;
            $this->msg = 'Dokumentet er allerede utlånt i Alma.';

            return is_null($almaLoan);
        }

        // Always true if the item is a generic representation without barcode
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
        return $this->msg;
    }
}
