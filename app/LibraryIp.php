<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LibraryIp extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'library_ips';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['library_id', 'ip'];

    public function library()
    {
        return $this->belongsTo(Library::class);
    }
}
