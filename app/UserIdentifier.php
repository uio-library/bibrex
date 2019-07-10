<?php declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserIdentifier extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'value', 'type'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the user that this identifier belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
