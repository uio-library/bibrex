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
            ['name' => 'BIBSYS-dokument', 'library_id' => NULL, 'created_at' => new DateTime, 'updated_at' => new DateTime],
            ['name' => 'PS3-kontroller', 'library_id' => 1, 'created_at' => new DateTime, 'updated_at' => new DateTime],
            ['name' => 'Skjøteledning', 'library_id' => 1, 'created_at' => new DateTime, 'updated_at' => new DateTime],
        ]);
    }
}
