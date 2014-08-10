<?php

class Loan extends Eloquent {
	protected $guarded = array();
	protected $softDelete = true;
	public $errors;

	public function user()
	{
		return $this->belongsTo('User');
	}

	public function document()
	{
		return $this->belongsTo('Document');
	}

	public function reminders()
	{
		return $this->hasMany('Reminder');
	}

	public function library()
	{
		return $this->belongsTo('Library');
	}

	public function representation($plaintext = false)
	{
		if ($this->document->thing->id == 1) {
			$s = rtrim($this->document->title,' :')
				. ($this->document->subtitle ? ' : ' . $this->document->subtitle : '');
			if (!$plaintext) {
				$s .= ' <small>(' . $this->document->dokid . ')</small>';
			}
			return $s;
		} else {
			return $this->document->thing->name;
		}
	}

	public function daysLeft() {
		if (is_null($this->due_at)) {
			return 999999;
		}
		$d1 = new DateTime($this->due_at);
		$d2 = new DateTime();
		$diff = $d2->diff($d1);
		$dl = intval($diff->format('%r%a'));
		if ($dl > 0) $dl++;
		return $dl;
	}

	public function daysLeftFormatted() {
		$d = $this->daysLeft();
		if ($d == 999999) 
			return '';
		if ($d > 1)
			return '<span style="color:green;">Forfaller om ' . $d . ' dager</span>';
		if ($d == 1)
			return '<span style="color:orange;">Forfaller i morgen</span>';
		if ($d == 0)
			return '<span style="color:orange;">Forfaller i dag</span>';
		if ($d == -1)
			return '<span style="color:red;">Forfalt i går</span>';
		return'<span style="color:red;">Forfalt for ' . abs($d) . ' dager siden</span>';
	}

	private function ncipCheckout() {

		$results = DB::select('SELECT ltid, in_bibsys FROM users WHERE users.id = ?', array($this->user_id));
		if (empty($results)) dd("user not found");
		$user = $results[0];

		$ltid = $user->ltid;
		$this->as_guest = false;
		if (!$user->in_bibsys) {

			$lib = Auth::user();

			if (is_null($ltid) && !array_get($lib->options, 'guestcard_for_cardless_loans', false)) {
				$this->errors->add('cardless_not_activated', 'Kortløse utlån er ikke aktivert. Det kan aktiveres i <a href="' . action('LibrariesController@myAccount') . '">kontoinnstillingene</a>.');
				return false;
			}

			if (!is_null($ltid) && !array_get($lib->options, 'guestcard_for_nonworking_cards', false)) {
				$this->errors->add('guestcard_not_activated', 'Kortet ble ikke funnet i BIBSYS og bruk av gjestekort er ikke aktivert. Det kan aktiveres i <a href="' . action('LibrariesController@myAccount') . '">kontoinnstillingene</a>.');
				return false;
			}

			if (is_null($lib->guest_ltid)) {
				$this->errors->add('guestcard_not_configured', 'Gjestekortnummer er ikke satt opp i <a href="' . action('LibrariesController@myAccount') . '">kontoinnstillingene</a>.');
				return false;
			}

			$ltid = $lib->guest_ltid;
			$this->as_guest = true;

		}

		$results = DB::select('SELECT things.id, documents.dokid FROM things,documents WHERE things.id = documents.thing_id AND documents.id = ?', array($this->document_id));
		if (empty($results)) dd("thing not found");

		$thing = $results[0];
		$dokid = $thing->dokid;

		if ($thing->id == 1) {

			$ncip = App::make('ncip.client');
			$response = $ncip->checkOutItem($ltid, $dokid);

			// BIBSYS sometimes returns an empty response on successful checkouts.
			// We will therefore threat an empty response as success... for now...
			$logmsg = '[NCIP] Lånte ut ' . $dokid . ']] til ' . $ltid . '';
			if ($this->as_guest) {
				$logmsg .= ' (midlertidig lånekort)';
			}
			$logmsg .= ' i BIBSYS.';
			if ((!$response->success && $response->error == 'Empty response') || ($response->success)) {
				if ($response->dueDate) {
					$this->due_at = $response->dueDate;
					$logmsg .= ' Fikk forfallsdato.';
				} else {
					$logmsg .= ' Fikk tom respons.';
				}
				Log::info($logmsg);
			} else {
				Log::info('Dokumentet [[Document:' . $dokid . ']] kunne ikke lånes ut i BIBSYS: ' . $response->error);
				$this->errors->add('checkout_error', 'Dokumentet kunne ikke lånes ut i BIBSYS: ' . $response->error);
				return false;
			}

		}
		return true;
	}

	/**
	 * Save the model to the database.
	 *
	 * @param  array  $options
	 * @return bool
	 */
	public function save(array $options = array())
	{
		$this->errors = new Illuminate\Support\MessageBag;
		if (!$this->exists) {

			// Set library id
			$this->library_id = Auth::user()->id;

			// Checkout in NCIP service
			if (!$this->ncipCheckout()) {
				return false;
			}
		}

		parent::save($options);
		return true;
	}

	/**
	 * Check in the document in NCIP and delete the loan
	 *
	 * @return null
	 */
	public function checkIn()
	{
		if ($this->document->thing->id == 1) {

			$dokid = $this->document->dokid;

			$ncip = App::make('ncip.client');
			$response = $ncip->checkInItem($dokid);

			if (!$response->success) {
				Log::error('Dokumentet ' . $dokid . ' kunne ikke leveres inn i BIBSYS: ' . $response->error);
				dd("Dokumentet kunne ikke leveres inn i BIBSYS: " . $response->error);
			}
			Log::info('[NCIP] Returnerte ' . $dokid . ' i BIBSYS');
		}
		$this->delete();
	}

	/**
	 * Restore a soft-deleted model instance.
	 *
	 * @return bool|null
	 */
	public function restore()
	{
		if (!$this->ncipCheckout()) {
			return false;
		}
		parent::restore();
		return true;
	}

	public function transfer()
	{
		if ($this->as_guest) {
			$dokid = $this->document->dokid;
			$ltid = $this->user->ltid;

			$ncip = App::make('ncip.client');
			$ncip->checkInItem($dokid);
			$response = $ncip->checkOutItem($ltid, $dokid);
			if ($response->success) {
				$this->as_guest = false;
				$this->save();
				Log::info('[NCIP] Overførte lånet av ' . $dokid . ' til ' . $ltid . ' i BIBSYS');
			} else {
				Log::error('[NCIP] Klarte ikke å overføre lånet av ' . $dokid . ' til ' . $tlid . ' i BIBSYS');
				return $response->error;
			}
		}
		return true;
	}

}
