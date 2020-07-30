<?php

namespace App;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thing extends Model
{

    use SoftDeletes;

    protected $guarded = array();

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'image' => 'object',
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function settings()
    {
        return $this->hasMany(ThingSettings::class);
    }

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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['properties', 'note', 'image'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['library_settings'];

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
     * The settings for this thing at my library.
     *
     * @return ThingSettings
     */
    public function getLibrarySettingsAttribute(Library $library = null)
    {
        $library = $library ?? \Auth::user();

        return $this->settings()
            ->where('library_id', $library->id)
            ->first() ?? ThingSettings::make([
                'library_id' => $library->id,
                'thing_id' => $this->id,
            ]);
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

    public function name($lang = 'nob')
    {
        return $this->properties->get("name.$lang");
    }

    public function getPropertiesAttribute()
    {
        return new ThingProperties(json_decode($this->attributes['properties'], true));
    }

    public function setPropertiesAttribute($value)
    {
        $this->attributes['properties'] = json_encode($value);
    }
}
