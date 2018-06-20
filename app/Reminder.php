<?php

namespace App;

use App\Mail\FirstReminder;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
	public function loan()
	{
		return $this->belongsTo(Loan::class);
	}
}
