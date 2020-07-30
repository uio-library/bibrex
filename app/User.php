<?php

namespace App;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
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
    protected $fillable = ['in_alma', 'firstname', 'lastname', 'phone', 'email', 'lang'];

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
        'lastname', 'firstname', 'phone', 'email', 'lang', 'note'
    ];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Find user from some identifier (Alma primary id, barcode or other).
     *
     * @param string $identifier
     * @return User|null
     */
    public static function fromIdentifier($identifier)
    {
        if (empty($identifier)) {
            return null;
        }
        $user = User::where('alma_primary_id', '=', $identifier)->first();
        if (is_null($user)) {
            $user = User::whereHas('identifiers', function (Builder $query) use ($identifier) {
                $query->where('value', '=', $identifier);
            })->first();
        }

        return $user;
    }

    public function identifiers()
    {
        return $this->hasMany(UserIdentifier::class);
    }

    public function getAllIdentifierValues()
    {
        $values = [];
        if ($this->alma_primary_id) {
            $values[] = $this->alma_primary_id;
        }
        foreach ($this->identifiers as $identifier) {
            $values[] = $identifier['value'];
        }
        return $values;
    }

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
        $merged['identifiers'] = [];
        foreach ($this->identifiers()->get(['type', 'value']) as $identifier) {
            $merged['identifiers'][] = $identifier;
        }
        foreach ($user->identifiers()->get(['type', 'value']) as $identifier) {
            $merged['identifiers'][] = $identifier;
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
            if ($key != 'identifiers') {
                $this->$key = $val;
            }
        }

        $this->save();

        $this->setIdentifiers($data['identifiers']);

        return null;
    }

    public function hasFees()
    {
        return $this->fees !== 0;
    }

    /**
     * Update unique value. If the value clashes with another user, delete the other user if it has no loans.
     *
     * @param array $identifiers
     * @param bool $deleteOnConflict
     */
    public function setIdentifiers(array $identifiers, bool $deleteOnConflict = false)
    {
        $currentValues = $this->identifiers->pluck('value')->toArray();

        $newIdentifiers = [];
        foreach ($identifiers as $identifier) {
            $newIdentifiers[$identifier['value']] = $identifier['type'];
        }

        $newValues = array_keys($newIdentifiers);

        $valuesToRemove = array_diff($currentValues, $newValues);
        $valuesToAdd = array_diff($newValues, $currentValues);

        $toAdd = array_map(function ($value) use ($newIdentifiers) {
            return [
                'user_id' => $this->id,
                'value' => $value,
                'type' => $newIdentifiers[$value],
            ];
        }, $valuesToAdd);

        // Check uniqueness
        foreach ($toAdd as $identifier) {
            $model = UserIdentifier::where('value', '=', $identifier['value'])->first();
            if (!is_null($model) && $model->user_id !== $this->id) {
                if ($deleteOnConflict) {
                    $this->deleteOtherUserIfPossible($model->user, $identifier['value']);
                } else {
                    throw new \RuntimeException(
                        "ID-konflikt for verdien {$identifier['value']}, og kan ikke slette {$model->user->id}."
                    );
                }
            }
        }

        if (count($valuesToRemove) || count($valuesToAdd)) {
            $msg = sprintf(
                'Oppdaterte identifikatorer for <a href="%s">%s</a>.',
                action('UsersController@getShow', $this->id),
                $this->name
            );

            // Delete
            if (count($valuesToRemove)) {
                $msg .= sprintf(
                    ' Fjernet: %s.',
                    implode(', ', $valuesToRemove)
                );
                UserIdentifier::where('user_id', '=', $this->id)->whereIn('value', $valuesToRemove)->delete();
            }

            // Add
            if (count($valuesToAdd)) {
                $msg .= sprintf(
                    ' La til: %s.',
                    implode(', ', $valuesToAdd)
                );
                UserIdentifier::insert($toAdd);
            }

            \Log::info($msg);
        }
    }

    /**
     * Set Alma Primary Id. If the value clashes with another user, delete the other user if it has no loans.
     *
     * @param string $value
     * @param bool $deleteOnConflict
     */
    public function setAlmaPrimaryId(string $value, $deleteOnConflict = false)
    {
        // Check for uniqueness
        if (is_null($this->id)) {
            // Model not saved yet
            $otherUser = User::where('alma_primary_id', '=', $value)->first();
        } else {
            $otherUser = User::where('alma_primary_id', '=', $value)->where('id', '!=', $this->id)->first();
        }

        if (!is_null($otherUser)) {
            if ($deleteOnConflict) {
                $this->deleteOtherUserIfPossible($otherUser, $value);
            } else {
                throw new \RuntimeException(
                    "ID-konflikt for verdien {$value}, og kan ikke slette {$otherUser->id}."
                );
            }
        }

        $this->alma_primary_id = $value;
    }

    protected function deleteOtherUserIfPossible(User $otherUser, string $value)
    {
        if (!$otherUser->loans->count()) {
            if (!is_null($this->id)) {
                $localRef = "brukeren med ID {$this->id}";
            } else {
                $localRef = "(ny bruker)";
            }
            \Log::warning(
                "Verdien '{$value}' er i bruk som identifikator for flere brukere. " .
                "Sletter brukeren med ID {$otherUser->id} (som ikke hadde noen lån), beholder $localRef."
            );
            $otherUser->delete();
        } else {
            throw new \RuntimeException(
                "Verdien '{$value}' er i bruk som identifikator for flere brukere. ".
                "Kan ikke slette brukeren {$otherUser->id} fordi brukeren har lån."
            );
        }
    }
}
