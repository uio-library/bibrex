<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table) {
			$table->increments('id');
			$table->string('ltid')->unique()->nullable();
			$table->string('lastname')->nullable();
			$table->string('firstname')->nullable();
			$table->string('phone')->nullable();
			$table->string('email')->nullable();
			$table->boolean('in_bibsys');
			$table->date('birth')->nullable();
			$table->enum('lang', array('nor','eng'))->nullable();
			$table->string('note')->nullable();
			$table->dateTime('note_date')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
