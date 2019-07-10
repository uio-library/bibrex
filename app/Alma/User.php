<?php

namespace App\Alma;

use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Exception\ResourceNotFound;
use Scriptotek\Alma\Users\User as AlmaUser;

class User
{
    public $primaryId;
    public $barcodes;
    public $universityIds;
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

    /**
     * Lookup user by primary or non-primary identifier.
     *
     * @param AlmaClient $alma
     * @param $identifier
     * @return User|null
     */
    public static function lookup(AlmaClient $alma, $identifier)
    {
        if (empty($identifier)) {
            return null;
        }
        try {
            return new self($alma->users->get($identifier)->init());
        } catch (ResourceNotFound $e) {
            return null;
        }
    }

    public static function isUserBarcode($value)
    {
        // 2-5 letters followed by 5-8 digits, making up 10 characters in total.
        return preg_match('/^[a-zA-Z]{2}[0-9a-zA-Z]{3}[0-9a-zA-Z]{5}$/', $value);
    }

    public function getBarcodes()
    {
//        if (self::isUserBarcode($this->primaryId)) {
//            return $this->primaryId;
//        }
        return $this->user->barcodes;
    }

    public function getUniversityIds()
    {
        return $this->user->universityIds;
    }

    public function getFees()
    {
        return $this->user->fees->total_sum;
    }

    public function getIdentifiers()
    {
        return $this->user->identifiers;
    }
}
