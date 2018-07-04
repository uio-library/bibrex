<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thing extends Model
{

    use SoftDeletes;

    protected $guarded = array();

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['at_my_library', 'library_settings'];

    public function activeLoans()
    {
        $loans = array();
        foreach ($this->items as $item) {
            foreach ($item->loans as $loan) {
                $loans[] = $loan;
            }
        }
        return $loans;
    }

    /**
     * The libraries where the thing is available.
     */
    public function libraries()
    {
        return $this->belongsToMany(Library::class)
            ->withPivot('require_item', 'send_reminders')
            ->using(LibraryThing::class);
            //->withPivot('require_item');
    }

    /**
     * Whether the thing is activated at my library.
     *
     * @return bool
     */
    public function getAtMyLibraryAttribute()
    {
        return $lib = $this->libraries()
            ->where('library_id', \Auth::user()->id)
            ->first() ? true : false;
    }

    /**
     * The settings for this thing at my library.
     *
     * @return bool
     */
    public function getLibrarySettingsAttribute()
    {
        $lib = $this->libraries()
            ->where('library_id', \Auth::user()->id)
            ->first();
        return $lib ? $lib->pivot->only('require_item', 'send_reminders') : [];
    }

    public function availableItems()
    {
        return $this->items()->count() - count($this->activeLoans());
    }

    public function allLoans()
    {
        $loans = array();
        foreach ($this->items as $item) {
            foreach ($item->allLoans as $loan) {
                $loans[] = $loan;
            }
        }
        return $loans;
    }

    public function getProperty($key)
    {
        return array_get($this->properties, $key);
    }

    public function setProperties($values)
    {
        $props = $this->properties;
        foreach ($values as $k => $v) {
            array_set($props, $k, $v);
        }
        $this->properties = $props;
    }
}
