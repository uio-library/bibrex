<?php

class Reminder extends Eloquent {
	protected $guarded = array();

	public static $rules = array();

	public function loan()
    {
        return $this->belongsTo('Loan');
    }
}
