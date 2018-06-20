<?php

use Illuminate\Database\Seeder;

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
            ['name' => 'BIBSYS-dokument', 'created_at' => new DateTime, 'updated_at' => new DateTime],
            ['name' => 'PS3-kontroller', 'created_at' => new DateTime, 'updated_at' => new DateTime],
            ['name' => 'Skjøteledning', 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ]);
    }
}
