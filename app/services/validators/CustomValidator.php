<?php namespace App\Services\Validators;

use Illuminate\Validation\Validator;

class CustomValidator extends Validator {

    public function validateStartsWithUo($attribute, $value, $parameters)
    {
    	if (!isset($this->data['thing']) || ($this->data['thing'] != '1')) {  // Ikke bibsys-dok:
    		return true;
    	}
    	if (!preg_match('/[0-9]/', $value)) { // Inneholder ikke tall: antakelig et navn
    		return true;
    	}
    	return substr($value, 0, 2) == 'uo';

    }

}