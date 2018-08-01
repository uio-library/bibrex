<?php

namespace App\Console\Commands;

use App\Loan;
use Illuminate\Console\Command;
use Scriptotek\Alma\Client as AlmaClient;

class SyncLoans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bibrex:sync-loans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check in loans that have been returned in Alma';

    /** @var AlmaClient */
    protected $alma;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AlmaClient $alma)
    {
        parent::__construct();
        $this->alma = $alma;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (Loan::with('item', 'item.thing')->get() as $loan) {
            if ($loan->item->thing_id == 1) {
                $this->processLoan($loan);
            }
        }
    }

    protected function processLoan(Loan $loan)
    {
        $barcode = $loan->item->barcode;
        $library = $loan->library;

        echo " - $barcode: ";

        $errBecause = sprintf(
            'Kunne ikke sjekke lån av <a href="%s">%s</a> mot Alma fordi',
            action('ItemsController@show', $loan->item_id),
            $barcode
        );

        $sucBecause = sprintf(
            'Fjerner lokalt lån av <a href="%s">%s</a> fordi',
            action('ItemsController@show', $loan->item_id),
            $barcode
        );

        if (is_null($library->temporary_barcode)) {
            $this->line('konfigfeil');
            \Log::error("$errBecause biblioteket ikke lenger har et midlertidig lånekort.");
            return;
        }

        $tempUser = $this->alma->users[$library->temporary_barcode];

        if (is_null($tempUser)) {
            $this->line('konfigfeil');
            \Log::error("$errBecause brukeren '{$library->temporary_barcode}' ikke ble funnet i Alma.");
            return;
        }

        $almaItem = $this->alma->items->fromBarcode($barcode);
        $almaLoan = $almaItem->loan;

        if (is_null($almaLoan)) {
            $this->line('returnert i Alma');

            // Delete local loan and item
            $loan->checkIn();

            \Log::info("$sucBecause dokumentet har blitt returnert i Alma.");
            return;
        }

        if ($almaLoan->user_id != $library->temporary_barcode) {
            $this->line('utlånt til annen person i Alma');

            // Delete local loan and item
            $loan->checkIn();

            \Log::info("$sucBecause dokumentet har blitt utlånt til en annen person i Alma.");
            return;
        }

        $this->line('fremdeles utlånt i Alma');
    }
}
