<?php

namespace App\Console\Commands;

use App\Loan;
use App\User;
use Illuminate\Console\Command;

class Anonymize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bibrex:anonymize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Anonymize returned loans.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $anonUser = User::firstOrCreate([
            'lastname' => '(anonymisert)',
            'firstname' => '(anonymisert)',
        ]);

        $loans = Loan::withTrashed()->whereNotNull('deleted_at')
            ->where('user_id', '!=', $anonUser->id)
            ->where('is_lost', '=', false)
            ->with('user')
            ->get();

        $c = 0;
        foreach ($loans as $loan) {
            $loan->user_id = $anonUser->id;
            $loan->save();
            $c++;
        }
        if ($c > 0) {
            \Log::info("Anonymiserte $c lån");
        }
    }
}
