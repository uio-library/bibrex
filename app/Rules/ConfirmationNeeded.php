<?php

namespace App\Rules;

use App\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

class ConfirmationNeeded implements Rule
{
    protected $problems = [];

    /**
     * Create a new rule instance.
     *
     * @param User|null $user
     */
    public function __construct(User $user = null)
    {
        if (is_null($user)) {
            return;
        }

        if ($user->hasFees()) {
            $this->problems[] = sprintf('Brukeren har %d,- i utestående gebyr i Alma.', $user->fees);
        }
        if (count($user->blocks)) {
            $msgs = array_values(array_map(
                function ($b) {
                    return Arr::get($b, 'block_description.desc');
                },
                $user->blocks
            ));
            $this->problems[] = sprintf(
                'Brukeren har følgende merknader: <ul><li>%s</li></ul>',
                implode('</li><li>', $msgs)
            );
        }
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
        if (!count($this->problems)) {
            return true;
        }
        if ($value == 'checked') {
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
        return "<p>" . implode("</p><p>", $this->problems) . "</p>";
    }
}
