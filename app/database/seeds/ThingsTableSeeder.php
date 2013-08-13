<?php

class ThingsTableSeeder extends Seeder {

	public function run()
	{
		// Uncomment the below to wipe the table clean before populating
		//DB::table('things')->truncate();
		DB::table('things')->delete();

		$things = array(
			array('name' => 'bibsysdok', 'label' => 'BIBSYS-dokument', 'created_at' => new DateTime, 'updated_at' => new DateTime),
			array('name' => 'ps3kontroller', 'label' => 'PS3-kontroller', 'created_at' => new DateTime, 'updated_at' => new DateTime),
			array('name' => 'skjoteledning', 'label' => 'Skjøteledning', 'created_at' => new DateTime, 'updated_at' => new DateTime),
		);

		// Uncomment the below to run the seeder
		DB::table('things')->insert($things);
	}

}
