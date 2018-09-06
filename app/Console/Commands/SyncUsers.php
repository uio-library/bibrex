<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

use PDOException;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Exception\RequestFailed;

class SyncUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bibrex:sync-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch changes to Alma users and try to link unlinked users.';

    /** @var AlmaClient */
    protected $alma;

    /**
     * Create a new command instance.
     *
     * @param AlmaClient $alma
     */
    public function __construct(AlmaClient $alma)
    {
        parent::__construct();
        $this->alma = $alma;
    }

    /**
     * Update a single user
     *
     * @param User $user
     */
    protected function processUser(User $user)
    {
        echo " - $user->name: ";

        if ($user->in_alma) {
            if ($user->updateFromAlma($this->alma)) {
                if ($user->isDirty()) {
                    \Log::info(sprintf(
                        'Oppdaterte brukeren <a href="%s">%s</a> fra Alma.',
                        action('UsersController@getShow', $user->id),
                        $user->name
                    ));

                    $this->info('oppdatert');
                } else {
                    $this->line('ingen endringer');
                }
            } else {
                \Log::warning(sprintf(
                    'Brukeren <a href="%s">%s</a> ble ikke lenger funnet i Alma!',
                    action('UsersController@getShow', $user->id),
                    $user->name
                ));

                $this->warn('ikke i Alma lenger');
            }
        } else {
            if ($user->updateFromAlma($this->alma)) {
                \Log::info(sprintf(
                    'Lenket brukeren <a href="%s">%s</a> til en Alma-bruker.',
                    action('UsersController@getShow', $user->id),
                    $user->name
                ));

                $this->info('lenket til Alma-bruker');
                $this->transferLoans($user);
            } else {
                $this->line('ikke i Alma');
            }
        }
        try {
            $user->save();
        } catch (PDOException $e) {
            $this->error('Konflikt!');
            \Log::warning(sprintf(
                'Brukeren <a href="%s">%s</a> kunne ikke lagres på grunn av en konflikt - to brukere har ' .
                'samme strekkode eller feide-id. Sjekk i brukerlista om det er to brukere som kan slås sammen.',
                action('UsersController@getShow', $user->id),
                $user->name
            ));
            return;
        }

        if ($user->in_alma) {
            // Check if user have loans of thing_id 1 and transfer them if so.
            // In case the user was manually synced during the day.
            $tempLoans = $user->loans()->whereHas('item', function ($query) {
                $query->where('thing_id', 1);
            })->count();

            if ($tempLoans) {
                $this->transferLoans($user);
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (User::get() as $user) {
            $this->processUser($user);
        }
    }

    protected function transferLoans(User $localUser)
    {
        $almaUser = $this->alma->users[$localUser->alma_primary_id];

        if (is_null($almaUser)) {
            \Log::error("Kunne ikke overføre lån fordi Alma-brukeren ikke ble funnet. Meget uventet.");
            return;
        }

        $n = 0;

        foreach ($localUser->loans as $loan) {
            if ($loan->item->thing_id == 1) {
                // Loan should be transferred from the temporary card to the user

                $barcode = $loan->item->barcode;
                $library = $loan->library;

                $errBecause = "Kunne ikke overføre lån av $barcode i Alma fordi";

                if (is_null($library->temporary_barcode)) {
                    \Log::error("$errBecause biblioteket ikke lenger har et midlertidig lånekort.");
                    continue;
                }

                if (is_null($library->library_code)) {
                    \Log::error("$errBecause biblioteket ikke lenger har en bibliotekskode.");
                    continue;
                }

                $tempUser = $this->alma->users[$library->temporary_barcode];
                $almaLibrary = $this->alma->libraries[$library->library_code];

                if (is_null($tempUser)) {
                    \Log::error("$errBecause brukeren '{$library->temporary_barcode}' ikke ble funnet i Alma.");
                    continue;
                }

                $almaItem = $this->alma->items->fromBarcode($barcode);
                $almaLoan = $almaItem->loan;

                if (is_null($almaLoan)) {
                    \Log::warning("$errBecause dokumentet i mellomtiden har blitt returnert i Alma.");

                    // Checkin local loan and delete temporary item
                    $loan->checkIn();

                    continue;
                }

                if ($almaLoan->user_id != $library->temporary_barcode) {
                    \Log::warning("$errBecause dokumentet ikke lenger er utlånt til {$library->temporary_barcode}.");

                    // Checkin local loan and delete temporary item
                    $loan->checkIn();

                    continue;
                }

                if (count($almaItem->requests)) {
                    \Log::warning("$errBecause dokumentet har reserveringer.");
                    continue;
                }

                // Cross fingers
                try {
                    $almaItem->scanIn($almaLibrary, 'DEFAULT_CIRC_DESK', [
                        'place_on_hold_shelf' => 'false',
                        'auto_print_slip' => 'false',
                    ]);
                    $almaItem->checkOut($almaUser, $almaLibrary);
                } catch (RequestFailed $e) {
                    \Log::warning($errBecause . ' ' . $e->getMessage());
                    continue;
                }

                \Log::info(sprintf(
                    'Overførte lån av <a href="%s">%s</a> til Alma-brukeren.',
                    action('ItemsController@show', $loan->item->id),
                    $barcode
                ));

                // Checkin local loan and delete temporary item
                $loan->checkIn();

                $n++;
            }
        }
        $this->info(" > Overførte $n lån");
    }
}
