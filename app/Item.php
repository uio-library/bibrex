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
    protected $fillable = ['dokid', 'library_id'];

    public function thing()
    {
        return $this->belongsTo(Thing::class, 'thing_id');
    }

    public function library()
    {
        return $this->belongsTo(Library::class, 'library_id');
    }

    public function loans()
    {
        return $this->hasMany(Loan::class)
            ->with('user');
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
        $this->is_lost = false;
        $this->save();
    }

    public function formattedLink($ucfirst = false)
    {
        $name = s($this->thing->properties->name_definite->nob);
        $name = $ucfirst ? $name->upperCaseFirst() : $name->lowerCaseFirst();

        return sprintf(
            '<a href="%s">%s</a>',
            action('ItemsController@show', $this->id),
            $name
        );
    }
}
