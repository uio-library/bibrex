<?php

class ThingsTableSeeder extends Seeder {

	public function run()
	{
		// Uncomment the below to wipe the table clean before populating
		//DB::table('things')->truncate();  // does not work with foreign key constraints
		// DB::table('things')->delete();    // does not reset autoincrementing indices

		$things = array(
			array('name' => 'BIBSYS-dokument', 'library_id' => NULL, 'created_at' => new DateTime, 'updated_at' => new DateTime),
			array('name' => 'PS3-kontroller', 'library_id' => 1, 'created_at' => new DateTime, 'updated_at' => new DateTime),
			array('name' => 'Skjøteledning', 'library_id' => 1, 'created_at' => new DateTime, 'updated_at' => new DateTime),
		);

		// Uncomment the below to run the seeder
		DB::table('things')->insert($things);
	}

}
