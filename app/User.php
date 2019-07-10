<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\MessageBag;

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

        // if (!empty($ltid) && !empty($user->ltid) && ($ltid != $user->ltid)) {
        //  $errors->add('ltid_conflict', "Kan ikke flette nytt LTID $ltid med eksisterende $user->ltid.");
        // }

        // if (!empty($ltid) && !empty($this->ltid) && ($ltid != $this->ltid)) {
        //  $errors->add('ltid_conflict', "Kan ikke flette nytt LTID $ltid med eksisterende $this->ltid.");
        // }

        if ($errors->count() > 0) {
            return $errors;
        }

        \Log::info('Slo sammen to brukere (ID ' . $user->id . ' og ' . $this->id . ')');

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
     * Update unique value. If the value clashes with another user, delete the other user if it has no loans.
     *
     * @param string $key
     * @param string $val
     */
    public function setUniqueValue(string $key, string $val)
    {
        // Check for uniqueness
        if (is_null($this->id)) {
            // Model not saved yet
            $otherUser = User::where($key, '=', $val)->first();
        } else {
            $otherUser = User::where($key, '=', $val)->where('id', '!=', $this->id)->first();
        }

        if (!is_null($otherUser)) {
            if (!$otherUser->loans->count()) {
                if (!is_null($this->id)) {
                    $localRef = "brukeren med ID {$this->id}";
                } else {
                    $localRef = "(ny bruker)";
                }
                \Log::warning(
                    "Verdien '{$val}' i bruk som {$key} for flere brukere. " .
                    "Sletter brukeren med ID {$otherUser->id} (som ikke hadde noen lån), beholder $localRef."
                );
                $otherUser->delete();
            } else {
                \Log::warning(
                    "Verdien '{$val}' i bruk som {$key} for flere brukere, men ".
                    "kan ikke slette brukeren {$otherUser->id}, fordi brukeren har lån."
                );
            }
        }

        $this->{$key} = $val;
    }
}
