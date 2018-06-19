<?php

use Illuminate\Support\Facades\Schema;
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

			$table->string('barcode')->unique()->nullable();
            $table->string('university_id')->unique()->nullable();

			$table->string('lastname')->nullable();
			$table->string('firstname')->nullable();
			$table->string('phone')->nullable();
			$table->string('email')->nullable();
			$table->boolean('in_alma')->default(0);
			$table->date('birth')->nullable();
			$table->enum('lang', array('nob', 'nno', 'eng'))->nullable();
			$table->string('note')->nullable();
			$table->dateTime('note_date')->nullable();
			$table->integer('loan_count')->unsigned()->default(0);
            $table->rememberToken();
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
