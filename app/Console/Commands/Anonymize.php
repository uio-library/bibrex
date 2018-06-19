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
    protected $signature = 'anonymize';

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
        $anonUser = User::where(['lastname' => '(anonymisert)', 'firstname' => '(anonymisert)'])->firstOrCreate();
        foreach (Loan::withTrashed()->with('user')->get() as $loan) {
            if (!is_null($loan->deleted_at)) {
                $loan->user_id = $anonUser->id;
                $loan->save();
            }
        }
    }
}
