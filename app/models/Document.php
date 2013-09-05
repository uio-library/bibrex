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
        return $this->hasMany('Loan');
    }

	public function allLoans()
	{
		return $this->hasMany('Loan')
			->with('document.thing')
			->withTrashed()
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
			$url = 'http://linode.biblionaut.net/services/sru_iteminfo.php?repo=bibsys&objektid=' . $this->objektid;

			$curl = New Curl;
			$data = $curl->simple_get($url);
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