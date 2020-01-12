<?php

use Illuminate\Support\Facades\Schema;
use App\Database\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLibraryThingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('library_thing', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('library_id')->unsigned();
            $table->integer('thing_id')->unsigned();

            $table->boolean('require_item')->default(true);
            $table->boolean('send_reminders')->default(true);

            $table->foreign('library_id')
                ->references('id')->on('libraries')
                ->onDelete('cascade');

            $table->foreign('thing_id')
                ->references('id')->on('things')
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
        Schema::dropIfExists('library_thing');
    }
}
