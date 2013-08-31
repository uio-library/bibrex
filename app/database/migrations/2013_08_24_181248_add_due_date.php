<?php

use Illuminate\Database\Migrations\Migration;

class AddDueDate extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('loans', function($table)
		{
			$table->dateTime('due_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('loans', function($table)
		{
			$table->dropColumn('due_at');
		});
	}

}