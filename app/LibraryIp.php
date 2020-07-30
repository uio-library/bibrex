<?php

namespace App;

use DateTimeInterface;
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

    public function library()
    {
        return $this->belongsTo(Library::class);
    }
}
