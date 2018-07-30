<?php

namespace App\Alma;

use Scriptotek\Alma\Users\User as AlmaUser;

class User
{
    public $primaryId;
    public $barcode;
    public $universityId;
    public $group;
    public $email;
    public $phone;
    public $lang;
    public $firstName;
    public $lastName;
    public $name;
    public $blocks;
    public $type = 'alma';

    protected $user;

    public static function isUserBarcode($value)
    {
        // 2-5 letters followed by 5-8 digits, making up 10 characters in total.
        return preg_match('/^[a-zA-Z]{2}[0-9a-zA-Z]{3}[0-9a-zA-Z]{5}$/', $value);
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
        $this->blocks = $user->user_block ?: [];
    }

    public function getBarcode()
    {
        if (self::isUserBarcode($this->primaryId)) {
            return $this->primaryId;
        }
        return $this->user->barcode;
    }

    public function getUniversityId()
    {
        return $this->user->universityId;
    }

    public function getFees()
    {
        return $this->user->fees->total_sum;
    }
}
