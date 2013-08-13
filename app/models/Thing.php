<?php

class Thing extends Eloquent {
	protected $guarded = array();

	public static $rules = array();

	public function documents()
    {
        return $this->hasMany('Document');
    }

}