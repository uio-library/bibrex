<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Scriptotek\Alma\Client as AlmaClient;

class TemporaryBarcodeExists implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(AlmaClient $alma)
    {
        $this->alma = $alma;
        $this->normalizedValue = null;
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
        if (empty($value)) {
            return true;
        }

        $almaUser = $this->alma->users->findOne('ALL~' . $value);
        if (!is_null($almaUser)) {
            $this->normalizedValue = $almaUser->getPrimaryId();
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Det midlertidige lånekortet må eksistere i Alma.';
    }

    public function getNormalizedValue()
    {
        return $this->normalizedValue;
    }
}
