<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use function Stringy\create as s;

class Item extends Model
{

    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['barcode', 'library_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'properties' => 'array',
    ];

    public function thing()
    {
        return $this->belongsTo(Thing::class, 'thing_id');
    }

    public function library()
    {
        return $this->belongsTo(Library::class, 'library_id');
    }

    public function activeLoan()
    {
        return $this->hasOne(Loan::class)
            ->with('user')
            ->whereNull('deleted_at');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class)
            ->with('user')
            ->orderBy('created_at', 'desc');
    }

    public function allLoans()
    {
        $library_id = \Auth::user()->id;

        return $this->hasMany(Loan::class)
            ->with('user')
            ->withTrashed()
            ->where('library_id', $library_id)
            ->orderBy('created_at', 'desc');
    }

    public function lost()
    {
        $this->is_lost = true;
        $this->save();
        $this->delete();
    }

    public function found()
    {
        $this->restore();

        if ($this->is_lost) {
            \Log::info(sprintf(
                'Registrerte %s som funnet.',
                $this->formattedLink(false, false)
            ), ['library' => \Auth::user()->name]);
            $this->is_lost = false;
            $this->save();
        }
    }

    public function formattedLink($ucfirst = false, $definite = true)
    {
        $name = s($this->thing->properties->get($definite ? 'name_definite.nob' : 'name_indefinite.nob'));
        $name = $ucfirst ? $name->upperCaseFirst() : $name->lowerCaseFirst();

        return sprintf(
            '<a href="%s">%s</a>',
            action('ItemsController@show', $this->id),
            $name
        );
    }
}
