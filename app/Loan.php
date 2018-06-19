<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\MessageBag;

class Loan extends Model {

    use SoftDeletes;

    protected $guarded = array();

    public $errors;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['due_at', 'deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    public function library()
    {
        return $this->belongsTo(Library::class);
    }

    public function representation($plaintext = false)
    {
        if ($this->item->thing->id == 1) {
            $s = rtrim($this->item->title,' :')
                . ($this->item->subtitle ? ' : ' . $this->item->subtitle : '');
            if (!$plaintext) {
                $s .= ' <small>(' . $this->item->dokid . ')</small>';
            }
            return $s;
        } else {
            if ($this->item->dokid) {
                return "{$this->item->thing->name} <tt>(#{$this->item->dokid})</tt>";
            } else {
                return $this->item->thing->name;
            }
        }
    }

    public function daysLeft() {
        if (is_null($this->due_at)) {
            return 999999;
        }
        $d1 = new \DateTime($this->due_at);
        $d2 = new \DateTime();
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

    public function relativeCreationTimeHours() {
        Carbon::setLocale('no');
        $hours = $this->created_at->diffInHours(Carbon::now());
        return $hours;
    }

    public function relativeCreationTime() {
        if ($this->user->lang == 'eng') {
            Carbon::setLocale('en');
            $msgs = [
                'justnow' => 'less than an hour ago',
                'today' => '{hours} hour(s) ago',
                'yesterday' => 'yesterday',
                '2days' => 'two days ago',
                'generic' => '{diff} ago',
            ];
        } else {
            Carbon::setLocale('no');
            $msgs = [
                'justnow' => 'for under en time siden',
                'today' => 'for {hours} time(r) siden',
                'yesterday' => 'i går',
                '2days' => 'i forgårs',
                'generic' => 'for {diff} siden',
            ];
        }

        $now = Carbon::now();
        $diffHours = $this->created_at->diffInHours($now);
        if ($diffHours < 1) {
            return $msgs['justnow'];
        }
        if ($now->dayOfYear - $this->created_at->dayOfYear == 1) {
            return $msgs['yesterday'];
        }
        if ($now->dayOfYear - $this->created_at->dayOfYear == 2) {
            return $msgs['2days'];
        }
        if ($diffHours < 48) {
            return str_replace('{hours}', $diffHours, $msgs['today']);
        }
        return str_replace('{diff}', $this->created_at->diffForHumans(Carbon::now(), true),
                           $msgs['generic']);
    }

    /*
    private function ncipCheckout() {

        $results = \DB::select('SELECT barcode, in_alma FROM users WHERE users.id = ?', array($this->user_id));
        if (empty($results)) dd("user not found");
        $user = $results[0];

        $ltid = $user->ltid;
        $this->as_guest = false;
        if (!$user->in_alma) {

            $lib = \Auth::user();

            if (is_null($ltid) && !array_get($lib->options, 'guestcard_for_cardless_loans', false)) {
                $this->errors->add('cardless_not_activated', 'Kortløse utlån er ikke aktivert. Det kan aktiveres i <a href="' . action('LibrariesController@getMyAccount') . '">kontoinnstillingene</a>.');
                return false;
            }

            if (!is_null($ltid) && !array_get($lib->options, 'guestcard_for_nonworking_cards', false)) {
                $this->errors->add('guestcard_not_activated', 'Kortet ble ikke funnet i BIBSYS og bruk av gjestekort er ikke aktivert. Det kan aktiveres i <a href="' . action('LibrariesController@getMyAccount') . '">kontoinnstillingene</a>.');
                return false;
            }

            if (is_null($lib->guest_ltid)) {
                $this->errors->add('guestcard_not_configured', 'Gjestekortnummer er ikke satt opp i <a href="' . action('LibrariesController@getMyAccount') . '">kontoinnstillingene</a>.');
                return false;
            }

            $ltid = $lib->guest_ltid;
            $this->as_guest = true;

        }

        $results = \DB::select('SELECT things.id, items.dokid FROM things,items WHERE things.id = items.thing_id AND items.id = ?', array($this->item_id));
        if (empty($results)) dd("thing not found");

        $thing = $results[0];
        $dokid = $thing->dokid;

        if ($thing->id == 1) {

            $ncip = \App::make('ncip.client');
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
                \Log::info($logmsg);
            } else {
                \Log::info('Dokumentet "' . $dokid . '" kunne ikke lånes ut i BIBSYS: ' . $response->error);
                $this->errors->add('checkout_error', 'Dokumentet kunne ikke lånes ut i BIBSYS: ' . $response->error);
                return false;
            }

        }
        return true;
    }
    */

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array())
    {
        $this->errors = new MessageBag();
        if (!$this->exists) {

            // Set library id
            $this->library_id = \Auth::user()->id;

            // Checkout in NCIP service
            // if (!$this->ncipCheckout()) {
            //  return false;
            // }
        }

        parent::save($options);
        return true;
    }

    /**
     * Check in the item in NCIP and delete the loan
     *
     * @return null
     */
    public function checkIn()
    {
        // if ($this->item->thing->id == 1) {

        //     $dokid = $this->item->dokid;

        //     $ncip = \App::make('ncip.client');
        //     $response = $ncip->checkInItem($dokid);

        //     if (!$response->success) {
        //         \Log::error('Dokumentet ' . $dokid . ' kunne ikke leveres inn i BIBSYS: ' . $response->error);
        //         dd("Dokumentet kunne ikke leveres inn i BIBSYS: " . $response->error);
        //     }
        //     \Log::info('[NCIP] Returnerte ' . $dokid . ' i BIBSYS');
        // }
        $this->delete();
    }

    /**
     * Restore a soft-deleted model instance.
     *
     * @return bool|null
     */
    // public function restore()
    // {
    //  // if (!$this->ncipCheckout()) {
    //  //  return false;
    //  // }
    //  parent::restore();
    //  return true;
    // }

    // public function transfer()
    // {
    //     if ($this->as_guest) {
    //         $dokid = $this->item->dokid;
    //         $barcode = $this->user->barcode;

    //         $ncip = \App::make('ncip.client');
    //         $ncip->checkInItem($dokid);
    //         $response = $ncip->checkOutItem($ltid, $dokid);
    //         if ($response->success) {
    //             $this->as_guest = false;
    //             $this->save();
    //             \Log::info('[NCIP] Overførte lånet av ' . $dokid . ' til ' . $ltid . ' i BIBSYS');
    //         } else {
    //             \Log::error('[NCIP] Klarte ikke å overføre lånet av ' . $dokid . ' til ' . $ltid . ' i BIBSYS');
    //             return $response->error;
    //         }
    //     }
    //     return true;
    // }

}
