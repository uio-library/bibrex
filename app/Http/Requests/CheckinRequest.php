<?php

namespace App\Http\Requests;

use App\Item;
use App\Loan;
use App\Rules\LoanExists;
use App\Rules\NeverLoanedOut;
use App\Rules\ThingExists;
use Illuminate\Foundation\Http\FormRequest;
use Scriptotek\Alma\Client as AlmaClient;

class CheckinRequest extends FormRequest
{
    public $item;
    public $user;
    public $localUser;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $library = \Auth::user();
        $alma = app(AlmaClient::class);

        // 1. If we're given a loan id, look for that.

        if ($this->input('loan')) {
            $loan = Loan::with(['item', 'item.thing', 'user'])
                ->find($this->input('loan'));
            if (is_null($loan)) {
                // Bail out
                return [
                    'loan' => [new LoanExists()],
                ];
            }
            // Success, we have located an active local loan!
            $this->loan = $loan;
            return [];
        }

        // 2. Otherwise, start by looking up the item by barcode, locally or in Alma.

        $barcode = $this->input('barcode');

        if (is_null($barcode)) {
            // Bail out, but no need for error, controller will take care of this case.
            return [];
        }

        $item = Item::withTrashed()->where('barcode', '=', $barcode)->first();

        if (is_null($item) && !empty($library->library_code)) {
            // Item doesn't exist locally, but perhaps in Alma?
            // If the library doesn't have a library code set, it means we should not check Alma.
            $almaItem = $alma->items->fromBarcode($barcode);
            if (is_null($almaItem)) {
                // Bail out, this thing really does not exist!
                return [
                    'barcode' => [new ThingExists()],
                ];
            }
            // Success, we have located an item in Alma that does not exist locally.
            $this->almaItem = $almaItem;
            return [];
        }

        // 3. At this point we have a local item (deleted or not). Let's see if it's on loan, or have been.

        $lastLoan = $item->loans()->withTrashed()->first();

        if (!is_null($lastLoan)) {
            $this->loan = $lastLoan; // This will be the newest one
            return [];
        }

        // 4. The item exists, but has never been loaned out.
        return [
            'barcode' => [new NeverLoanedOut($item)],
        ];
    }
}
