<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ExpandThingsTable2 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('things', function(Blueprint $table) {
            $table->string('email_name_definite_nor')->default('Fylles ut');
            $table->string('email_name_definite_eng')->default('Fill inn');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('things', function(Blueprint $table) {
			$table->dropColumn('email_name_definite_nor');
			$table->dropColumn('email_name_definite_eng');
		});
	}

}
