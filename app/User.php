<?php

namespace App;

use App\Alma\User as AlmaUser;
use App\Rules\NotGuestLtid;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\MessageBag;
use Scriptotek\Alma\Client as AlmaClient;

class User extends Authenticatable
{

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['barcode', 'university_id', 'in_alma', 'firstname', 'lastname', 'phone', 'email', 'lang'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'blocks' => 'array',
        'fees' => 'integer',
    ];

   /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['last_loan_at'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['name', 'url'];

    /**
     * Array of user-editable attributes (excluding machine-generated stuff)
     *
     * @static array
     */
    public static $editableAttributes = [
        'barcode', 'university_id','lastname', 'firstname', 'phone', 'email', 'lang', 'note'
    ];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function deliveredLoans()
    {
        return $this->hasMany(Loan::class)
            ->whereNotNull('deleted_at')
            ->with('item.thing')
            ->withTrashed()
            ->orderBy('created_at', 'desc');
    }

    public function getNameAttribute()
    {
        return $this->lastname . ', ' . $this->firstname;
    }

    public function getUrlAttribute()
    {
        return action('UsersController@getShow', $this->id);
    }

    /**
     * Mutuator for the barcode field
     *
     * @param  string  $value
     * @return void
     */
    public function setBarcodeAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['barcode'] = null;
        } else {
            $this->attributes['barcode'] = strtolower($value);
        }
    }

    /**
     * Merge in UserResponse data
     *
     * @param AlmaUser $au
     * @return void
     */
    public function mergeFromAlmaResponse(AlmaUser $au)
    {
        $this->in_alma = true;
        $this->alma_primary_id = $au->primaryId;
        $this->alma_user_group = $au->group;
        $this->barcode = $au->getBarcode();
        $this->university_id = $au->getUniversityId();
        $this->lastname = $au->lastName;
        $this->firstname = $au->firstName;
        $this->email = $au->email;
        $this->phone = $au->phone;
        $this->lang = $au->lang;
        $this->blocks = $au->blocks;
        $this->fees = $au->getFees();
    }

    protected function mergeAttribute($key, User $user)
    {
        return strlen($user->$key) > strlen($this->$key) ? $user->$key : $this->$key;
    }

    /**
     * Get data for merging $user into the current user. The returned
     * array can be passed directly to mergeWith or presented to a user
     * for review first.
     *
     * @param  User  $user
     * @return array
     */
    public function getMergeData($user)
    {
        $merged = array();
        foreach (static::$editableAttributes as $attr) {
            $merged[$attr] = $this->mergeAttribute($attr, $user);
        }
        return $merged;
    }

    /**
     * Merge in another user $user
     *
     * @param  User  $user
     * @param  array  $data  An array of merged attributes (optional)
     * @return \Illuminate\Support\MessageBag
     */
    public function merge(User $user, array $data = null)
    {

        if (is_null($data)) {
            $data = $this->getMergeData($user);
        }

        // Validate
        $errors = new MessageBag();
        $barcode = $data['barcode'];

        // if (!empty($ltid) && !empty($user->ltid) && ($ltid != $user->ltid)) {
        //  $errors->add('ltid_conflict', "Kan ikke flette nytt LTID $ltid med eksisterende $user->ltid.");
        // }

        // if (!empty($ltid) && !empty($this->ltid) && ($ltid != $this->ltid)) {
        //  $errors->add('ltid_conflict', "Kan ikke flette nytt LTID $ltid med eksisterende $this->ltid.");
        // }

        if ($errors->count() > 0) {
            return $errors;
        }

        \Log::info('Slo sammen to brukere (' . $user->id . ' og ' . $this->id . ')');

        foreach ($user->loans as $loan) {
            $loan->user_id = $this->id;
            $loan->save();
            \Log::info('Lån ' . $loan->id . ' flyttet fra bruker ' . $user->id . ' til ' . $this->id);
        }

        // Delete other user first to avoid database integrity conflicts
        $user->delete();

        // Update properties of the current user with merge data
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }

        $this->save();

        return null;
    }

    public function hasFees()
    {
        return $this->fees !== 0;
    }

    /**
     * Find the first Alma user matching a query.
     * @param AlmaClient $alma
     * @param array $queries
     * @return AlmaUser|null
     */
    protected function findAlmaUser(AlmaClient $alma, array $queries)
    {
        foreach ($queries as $query) {
            if (!empty($query[1])) {
                foreach ($alma->users->search($query[0] . '~' . $query[1], ['limit' => 1]) as $user) {
                    return new AlmaUser($user);
                }
            }
        }

        return null;
    }

    /**
     * Update the user object with fresh user data from Alma.
     * Returns true if successful, false if the user could no longer be found in Alma.
     *
     * @param AlmaClient $alma
     * @return bool
     */
    public function updateFromAlma(AlmaClient $alma)
    {
        $queries = [
            ['identifiers', $this->university_id],
            ['identifiers', $this->barcode],
            ['ALL', $this->university_id],
            ['ALL', $this->barcode],
        ];

        $almaUser = $this->findAlmaUser($alma, $queries);
        if (is_null($almaUser)) {
            $this->in_alma = false;
            return false;
        }

        $this->mergeFromAlmaResponse($almaUser);
        return true;
    }
}
