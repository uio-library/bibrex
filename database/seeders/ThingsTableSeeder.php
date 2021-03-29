<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('things')->insert([
            [
                'created_at' => new \DateTime,
                'updated_at' => new \DateTime,
                'properties' => json_encode([
                    'loan_time' => 30,
                    'name' => [
                        'nob' => 'Alma-dokument',
                        'nno' => 'Alma document',
                        'eng' => 'Alma document',
                    ],
                    'name_indefinite' => [
                        'nob' => 'et alma-dokument',
                        'nno' => 'eit alma-dokument',
                        'eng' => 'an alma document',
                    ],
                    'name_definite' => [
                        'nob' => 'alma-dokumentet',
                        'nno' => 'alma-dokumentet',
                        'eng' => 'the alma document',
                    ],
                ]),
            ],
            [
                'created_at' => new \DateTime,
                'updated_at' => new \DateTime,
                'properties' => json_encode([
                    'loan_time' => 1,
                    'name' => [
                        'nob' => 'Skjøteledning',
                        'nno' => 'Skøyteleidning',
                        'eng' => 'Extension coord',
                    ],
                    'name_indefinite' => [
                        'nob' => 'en skjøteledning',
                        'nno' => 'ein skøyteleidning',
                        'eng' => 'an extension coord',
                    ],
                    'name_definite' => [
                        'nob' => 'skjøteledningen',
                        'nno' => 'skøyteleidningen',
                        'eng' => 'the extension coord',
                    ],
                ]),
            ],
        ]);
    }
}
