<?php

use Illuminate\Database\Migrations\Migration;
use App\Database\Blueprint;

class ExpandThingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('things', function (Blueprint $table) {
            $table->boolean('send_reminders')->default(false);
            $table->string('email_name_nob')->default('Fylles ut');
            $table->string('email_name_nno')->default('Fylles ut');
            $table->string('email_name_eng')->default('Fill inn');
            $table->integer('num_items')->unsigned()->default(0);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('things', function (Blueprint $table) {
            $table->dropColumn('send_reminders');
            $table->dropColumn('email_name_nob');
            $table->dropColumn('email_name_nno');
            $table->dropColumn('email_name_eng');
            $table->dropColumn('num_items');
        });
    }
}
