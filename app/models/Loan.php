<?php

class Loan extends Eloquent {
	protected $guarded = array();
	protected $softDelete = true;
	public static $rules = array();

	public $guestNumber = 'umn1002157';

	public function user()
	{
		return $this->belongsTo('User');
	}

	public function document()
	{
		return $this->belongsTo('Document');
	}

	public function representation()
	{
		if ($this->document->thing->id == 1) {
			$s = rtrim($this->document->title,' :') 
				. ($this->document->subtitle ? ' : ' . $this->document->subtitle : '');
			$s .= ' <small>(' . $this->document->dokid . ')</small>';
			return $s;
		} else {
			return $this->document->thing->name;
		}
	}

	/**
	 * Save the model to the database.
	 *
	 * @param  array  $options
	 * @return bool
	 */
	public function save(array $options = array())
	{

		$guestNumber = array_get($options, 'guestNumber', $this->guestNumber);

		$results = DB::select('SELECT ltid, in_bibsys FROM users WHERE users.id = ?', array($this->user_id));
		if (empty($results)) {
			dd("user not found");
		}
		$user = $results[0];

		if ($user->ltid == $guestNumber) {
			$this->error = "Det midlertidige lånekortet skal aldri skannes i Bibrex. Hvis bruker ikke har lånekort skal man istedet oppgi personens navn.";
			return false;
		}
		$ltid = $user->in_bibsys ? $user->ltid : $guestNumber;

		$results = DB::select('SELECT things.id, documents.dokid FROM things,documents WHERE things.id = documents.thing_id AND documents.id = ?', array($this->document_id));
		if (empty($results)) {
			dd("thing not found");
		}
		$thing = $results[0];
		$dokid = $thing->dokid;

		if ($thing->id == 1) {

			$ncip = new NcipClient();
			$response = $ncip->checkOutItem($ltid, $dokid);

			if ($response->success) {
				if ($response->dueDate) {
					$this->due_at = $response->dueDate;
				}
			} else {
				$this->error = "Dokumentet kunne ikke lånes ut i BIBSYS: " . $response->error;
				return false;
			}

		}

		parent::save($options);
		return true;
	}

	/**
	 * Delete the model from the database.
	 *
	 * @return bool|null
	 */
	public function delete()
	{

		if ($this->document->thing->id == 1) {

			$dokid = $this->document->dokid;

			$ncip = new NcipClient();
			$response = $ncip->checkInItem($dokid);

			if (!$response['success']) {
				dd("Dokumentet kunne ikke leveres inn i BIBSYS: " . $response['error']);
			}
		}

		parent::delete();
	}

}