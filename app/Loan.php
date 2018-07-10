<?php

namespace App;

use App\Notifications\ExtendedDatabaseNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Support\MessageBag;

class Loan extends Model
{

    use SoftDeletes;

    protected $guarded = array();

    public $errors;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['due_at', 'deleted_at'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['url', 'created_at_relative', 'days_left'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class)
            ->withTrashed();
    }

    public function notifications()
    {
        return $this->hasMany(ExtendedDatabaseNotification::class)
            ->orderBy('created_at', 'desc');
    }

    public function library()
    {
        return $this->belongsTo(Library::class);
    }

    /**
     * Get library settings for the loaned thing.
     *
     * @return ThingSettings
     */
    public function getLibrarySettings()
    {
        return $this->item->thing->getLibrarySettingsAttribute($this->library_id);
    }

    public function representation($plaintext = false)
    {
        if ($this->item->thing->id == 1) {
            $s = rtrim($this->item->title, ' :')
                . ($this->item->subtitle ? ' : ' . $this->item->subtitle : '');
            if (!$plaintext) {
                $s .= ' <small>(' . $this->item->barcode . ')</small>';
            }
            return $s;
        } else {
            if ($this->item->barcode) {
                return "{$this->item->thing->name} <tt>(#{$this->item->barcode})</tt>";
            } else {
                return $this->item->thing->name;
            }
        }
    }

    public function daysLeft()
    {
        if (is_null($this->due_at)) {
            return 999999;
        }
        $d1 = $this->due_at;
        $d2 = Carbon::now();
        $days = $d2->diffInDays($d1, false);
        $hours = $d2->diffInHours($d1, false);

        if ($hours > 0) {
            $days++;
        }

        return $days;
    }

    public function getDaysLeftAttribute()
    {
        return $this->daysLeft();
    }

    public function getUrlAttribute()
    {
        return action('LoansController@getShow', ['loan' => $this->id]);
    }

    public function relativeCreationTimeHours()
    {
        Carbon::setLocale('no');
        $hours = $this->created_at->diffInHours(Carbon::now());
        return $hours;
    }

    public function relativeCreationTime()
    {
        if ($this->user->lang == 'eng') {
            Carbon::setLocale('en');
            $msgs = [
                'justnow' => 'just now',
                'today' => '{hours} hour(s) ago',
                'yesterday' => 'yesterday',
                '2days' => 'two days ago',
                'generic' => '{diff} ago',
            ];
        } else {
            Carbon::setLocale('no');
            $msgs = [
                'justnow' => 'nå nettopp',
                'today' => 'for {hours} time(r) siden',
                'yesterday' => 'i går',
                '2days' => 'i forgårs',
                'generic' => 'for {diff} siden',
            ];
        }

        $now = Carbon::now();
        $diffHours = $this->created_at->diffInHours($now);
        if ($diffHours < 1) {
            return $msgs['justnow'];
        }
        if ($now->dayOfYear - $this->created_at->dayOfYear == 1) {
            return $msgs['yesterday'];
        }
        if ($now->dayOfYear - $this->created_at->dayOfYear == 2) {
            return $msgs['2days'];
        }
        if ($diffHours < 48) {
            return str_replace('{hours}', $diffHours, $msgs['today']);
        }
        return str_replace(
            '{diff}',
            $this->created_at->diffForHumans(Carbon::now(), true),
            $msgs['generic']
        );
    }

    public function getCreatedAtRelativeAttribute()
    {
        return $this->relativeCreationTime();
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array())
    {
        $this->errors = new MessageBag();
        if (is_null($this->library_id)) {
            // Set library id
            $this->library_id = \Auth::user()->id;
        }

        parent::save($options);
        return true;
    }

    public function lost()
    {
        if ($this->item->barcode) {
            $this->item->lost();
        }

        $this->is_lost = true;
        $this->save();
        $this->delete();
    }

    public function found()
    {
        $this->restore();
        $this->is_lost = false;
        $this->save();

        if ($this->item->is_lost) {
            $this->item->found();
        }
    }

    /**
     * Check in the item.
     *
     * @return null
     */
    public function checkIn()
    {
        $this->delete();
    }
}
