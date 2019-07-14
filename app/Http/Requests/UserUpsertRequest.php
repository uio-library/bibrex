<?php

namespace App\Http\Requests;

use App\UserIdentifier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UserUpsertRequest extends FormRequest
{
    public $identifiers;

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'lastname.required' => 'Etternavn må fylles inn.',
            'firstname.required' => 'Fornavn må fylles inn.',
            'email.required_without' => 'Enten e-post eller telefonnummer må fylles inn.',
            'email.email' => 'E-postadresse må se ut som en e-postadresse.',
            'phone.regex' => 'Telefonnummeret må inneholde minst 8 tall, ' .
                'og ingen ikke-numeriske tegn bortsett fra et eventuelt plusstegn først.',
            'lang.required' => 'Språk må fylles inn.'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'lastname' => 'required',
            'firstname' => 'required',
            'phone' => 'nullable|regex:/^\+?[0-9]{8,}$/',
            'email' => 'nullable|email|required_without:phone',
            'note' => 'nullable',
            'lang' => 'required|in:eng,nob,nno',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $userId = $this->route('user')->id;
            $identifiers = [];
            foreach ($this->all() as $key => $val) {
                if (preg_match('/identifier_type_(new|[0-9]+)/', $key, $matches)) {
                    $id = $matches[1];
                    $identifier = [
                        'type' => $this->input('identifier_type_' . $id),
                        'value' => $this->input('identifier_value_' . $id),
                    ];
                    if (empty($identifier['value'])) {
                        continue;
                    }
                    $res = UserIdentifier::where('value', '=', $identifier['value'])
                        ->where('user_id', '!=', $userId)
                        ->first();

                    if (!is_null($res)) {
                        $validator->errors()->add(
                            'identifier_value_' . $id,
                            "Identifikatoren «{$identifier['value']}» er allerede i bruk av en annen bruker!"
                        );
                    }

                    $identifiers[] = $identifier;
                }
            }
            $this->identifiers = $identifiers;
        });
    }
}
