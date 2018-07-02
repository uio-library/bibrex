<?php

namespace App\Rules;

use App\User;
use Illuminate\Contracts\Validation\Rule;

class UserExists implements Rule
{
    protected $user;

    /**
     * Create a new rule instance.
     *
     * @param User $user
     */
    public function __construct(User $user = null)
    {
        $this->user = $user;
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
        return !is_null($this->user);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (!array_get(\Auth::user()->options, 'guestcard_for_cardless_loans', false)) {
            return 'Brukeren ble ikke funnet lokalt eller i Alma.' .
                ' Hvis du vil at Bibrex skal opprette lokale brukere i slike tilfeller kan du skru på dette i' .
                ' <a href="' . action('LibrariesController@getMyAccount') . '">kontoinnstillingene</a>.';
        }

        return 'Brukeren ble ikke funnet lokalt eller i Alma.';
    }
}
