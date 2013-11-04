<?php

use Illuminate\Support\MessageBag;
use Illuminate\Auth\UserInterface;

class LibraryIp extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'library_ips';

	public function library()
	{
		return $this->belongsTo('Library');
	}

}
