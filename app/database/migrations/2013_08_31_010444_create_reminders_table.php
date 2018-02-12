<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRemindersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('reminders', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('loan_id')->unsigned();
			$table->enum('medium', array('sms', 'email'))->default('email');
			$table->text('subject');
			$table->text('body');
			$table->timestamps();

			$table->foreign('loan_id')
				->references('id')->on('loans')
				->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('reminders');
	}

}
