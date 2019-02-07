<?php

use Illuminate\Database\Seeder;

class LibrariesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('libraries')->insert([
            'name' => 'Eksempelbiblioteket',
            'name_eng' => 'Example Library',
            'email' => 'post@eksempelbiblioteket.no',
            'password' => bcrypt('admin'),
            'password_changed' => new DateTime,
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
    }
}
