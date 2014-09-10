<?php

class Document extends Eloquent {
	protected $guarded = array();

	public static $rules = array();

	public function thing()
	{
		return $this->belongsTo('Thing');
	}

	public function loans()
	{
		return $this->hasMany('Loan')
			->with('user');
	}

	public function allLoans()
	{
		$library_id = Auth::user()->id;

		return $this->hasMany('Loan')
			->with('user')
			->withTrashed()
			->where('library_id', $library_id)
			->orderBy('created_at', 'desc');
	}

	/**
	 * Mutuator for the dokid field
	 *
	 * @param  string  $value
	 * @return void
	 */
	public function setDokidAttribute($value)
	{
		$this->attributes['dokid'] = strtolower($value);
	}

	/**
	 * Save the model to the database.
	 *
	 * @param  array  $options
	 * @return bool
	 */
	public function save(array $options = array())
	{

		if ($this->objektid) {
			$url = 'http://services.biblionaut.net/sru_iteminfo.php?repo=bibsys&objektid=' . $this->objektid;

			$curl = New Curl;
            $curl->cookie_file = storage_path('cookie_file');
            $curl->follow_redirects = false;
			$data = $curl->get($url);
			$data = json_decode($data);

			if (isset($data->title))
				$this->title = $data->title;
			if (isset($data->subtitle))
				$this->subtitle = $data->subtitle;
			if (isset($data->cover_image))
				$this->cover_image = $data->cover_image;
			if (isset($data->year))
				$this->year = $data->year;
		}

		parent::save($options);
	}

}
