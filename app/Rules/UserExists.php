<?php

namespace App\Rules;

use App\User;
use Illuminate\Contracts\Validation\Rule;

class UserExists implements Rule
{
    protected $alma;
    protected $user;

    /**
     * Create a new rule instance.
     *
     * @param User $user
     * @param array $suggestions
     * @param bool $alma
     */
    public function __construct(User $user = null, $suggestions = [], $alma = false)
    {
        $this->alma = $alma;
        $this->user = $user;
        $suggestions['user'] = '_new';
        $this->suggestions = $suggestions;
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
        $whereWeLooked = $this->alma ? ' lokalt eller i Alma' : '';

        if (isset($this->suggestions['barcode'])) {
            return sprintf(
                'Strekkoden ble ikke funnet%s. ' .
                'Sjekk om brukeren har fått nytt kort ved å søke på brukerens navn, eller ' .
                '<a href="%s">opprett en lokal bruker</a>.',
                $whereWeLooked,
                action('UsersController@getEdit', $this->suggestions)
            );
        }

        return sprintf(
            'Brukeren ble ikke funnet%s. ' .
            'Du kan registrere hen i <a href="%s" target="_blank">BIM</a> eller evt. ' .
            '<a href="%s">opprette en lokal bruker</a>.',
            $whereWeLooked,
            'https://bim.bibsys.no/',
            action('UsersController@getEdit', $this->suggestions)
        );
    }
}
