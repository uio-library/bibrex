<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('thing_id')->unsigned();

            $table->string('dokid')->unique()->nullable();
            $table->string('knyttid')->unique()->nullable();
            $table->string('objektid')->nullable();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('authors')->nullable();
            $table->string('year')->nullable();
            $table->string('cover_image')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->string('note')->nullable();


            $table->foreign('thing_id')
                ->references('id')->on('things')
                ->onDelete('cascade');

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
        Schema::drop('items');
    }
}
