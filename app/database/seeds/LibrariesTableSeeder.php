<?php

class LibrariesTableSeeder extends Seeder {

	public function run()
	{
		// Uncomment the below to wipe the table clean before populating
		//DB::table('users')->truncate();

		$libraries = array(
			array(
				'name' => 'Eksempelbiblioteket',
				'email' => 'post@eksempelbiblioteket.no',
				'password' => Hash::make('admin'),
				'password_changed' => new DateTime,
				'created_at' => new DateTime,
				'updated_at' => new DateTime
			),
		);

		// Uncomment the below to run the seeder
		DB::table('libraries')->insert($libraries);
	}

}
