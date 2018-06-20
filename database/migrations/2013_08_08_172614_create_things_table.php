<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateThingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('things', function(Blueprint $table) {
			$table->increments('id');
			$table->string('name')->unique();
			$table->string('note')->nullable();
			$table->integer('loan_time')->unsigned()->default(1);
			$table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('things');
	}

}
