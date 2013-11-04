<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

		// $this->call('UserTableSeeder');

	    //DB::statement('SET FOREIGN_KEY_CHECKS=0;');
	   /**
	    * You'll notice that I disable the foreign key constraints
	    * before and after running all my seeding. This may be bad
	    * practice but it's the only way I can use the truncate function
	    * to re-set the id count for each table.
	    */


		$this->call('LibrariesTableSeeder');
		$this->command->info('Libraries table seeded!');

		$this->call('LibraryIpsTableSeeder');
		$this->command->info('User ips table seeded!');

		$this->call('ThingsTableSeeder');
		$this->command->info('Things table seeded!');

	    //DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}