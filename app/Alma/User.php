<?php

namespace App\Alma;

use Scriptotek\Alma\Users\User as AlmaUser;

class User
{
    public $primaryId;
    public $group;
    public $email;
    public $phone;
    public $lang;
    public $firstName;
    public $lastName;
    public $name;
    public $barcode;
    public $university_id;
    public $type = 'alma';

    protected $user;
    protected $expanded = false;

    protected function isLTID($value)
    {
        return (                                            // If
            preg_match('/^[0-9a-zA-Z]{10}$/', $value) &&    // ... it's 10 characters long
            preg_match('/[0-9]{6,}/', $value)               // ... and contains at least six adjacent numbers
        );                                                  // ... we assume it's a LTID :)
    }


    public function __construct(AlmaUser $user)
    {
        $this->user = $user;

        foreach ($user->contact_info->email as $e) {
            if ($e->preferred) {
                $this->email = $e->email_address;
            }
        }
        foreach ($user->contact_info->phone as $e) {
            if ($e->preferred) {
                $this->phone = $e->phone_number;
            }
        }
        if (in_array($user->preferred_language->value, ['no', 'nb', 'nob'])) {
            $this->lang = 'nob';
        } elseif (in_array($user->preferred_language->value, ['nn', 'nno'])) {
            $this->lang = 'nno';
        } else {
            $this->lang = 'eng';
        }

        $this->firstName = $user->first_name;
        $this->lastName = $user->last_name;
        $this->primaryId = $user->primary_id;
        $this->group = $user->user_group->desc;
        $this->name = $this->lastName . ', ' . $this->firstName;
    }

    protected function expand()
    {
        if ($this->expanded) {
            return;
        }
        $this->user->fetch();
        $this->barcode = $this->user->getBarcode();
        $this->university_id = $this->user->getUniversityId();
    }

    public function getBarcode()
    {
        if ($this->isLTID($this->primaryId)) {
            return $this->primaryId;
        }
        $this->expand();
        return $this->barcode;
    }

    public function getUniversityId()
    {
        $this->expand();
        return $this->university_id;
    }
}
