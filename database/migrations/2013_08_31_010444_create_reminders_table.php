<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use App\Database\Blueprint;

class CreateRemindersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('loan_id')->unsigned();
            $table->enum('medium', array('sms', 'email'))->default('email');
            $table->string('type');
            $table->text('subject');
            $table->text('body');
            $table->string('sender_name');
            $table->string('sender_mail');
            $table->string('receiver_name');
            $table->string('receiver_mail');
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
