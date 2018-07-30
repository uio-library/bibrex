<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

use PDOException;
use Scriptotek\Alma\Client as AlmaClient;

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
    protected $description = 'Sync all users';

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
}
