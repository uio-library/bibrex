<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLibrariesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('libraries', function(Blueprint $table) {
			$table->increments('id', true);
			$table->string('name')->unique();
			$table->string('guest_ltid')->unique()->nullable();
			$table->string('email')->unique()->nullable();
			$table->string('password')->nullable();
			$table->dateTime('password_changed')->nullable();
			$table->timestamps();
		});

		Schema::table('users', function(Blueprint $table) {
			$table->integer('library_id')
				->unsigned()->nullable();
			$table->foreign('library_id')
				->references('id')->on('libraries')
				->onDelete('restrict');
		});

		Schema::table('things', function(Blueprint $table) {
			$table->integer('library_id')
				->unsigned()->nullable();
			$table->foreign('library_id')
				->references('id')->on('libraries')
				->onDelete('restrict');
		});

		Schema::table('loans', function(Blueprint $table) {
			$table->integer('library_id')
				->unsigned()->nullable();
			$table->foreign('library_id')
				->references('id')->on('libraries')
				->onDelete('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table) {
			$table->dropForeign('users_library_id_foreign');
			$table->dropColumn('library_id');
		});

		Schema::table('things', function(Blueprint $table) {
			$table->dropForeign('things_library_id_foreign');
			$table->dropColumn('library_id');
		});

		Schema::table('loans', function(Blueprint $table) {
			$table->dropForeign('loans_library_id_foreign');
			$table->dropColumn('library_id');
		});

		Schema::drop('libraries');
	}

}
