<?php

class LibraryIpsTableSeeder extends Seeder {

	public function run()
	{
		// Uncomment the below to wipe the table clean before populating
		//DB::table('users')->truncate();

		$ips = array(
			array('library_id' => 1,
				'ip' => getHostByName(getHostName()),
				'created_at' => new DateTime,
				'updated_at' => new DateTime
			),
		);

		// Uncomment the below to run the seeder
		DB::table('library_ips')->insert($ips);
	}

}
