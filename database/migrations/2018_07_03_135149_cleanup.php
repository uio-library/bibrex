<?php

use Illuminate\Support\Facades\Schema;
use App\Database\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Cleanup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('things', function (Blueprint $table) {
            $table->json('properties')->default('{}');
        });

        foreach (\DB::table('things')->get() as $thing) {
            $thing->properties = [
                'name_indefinite' => [
                    'nob' => $thing->email_name_nob,
                    'nno' => $thing->email_name_nno,
                    'eng' => $thing->email_name_eng,
                ],
                'name_definite' => [
                    'nob' => $thing->email_name_definite_nob,
                    'nno' => $thing->email_name_definite_nno,
                    'eng' => $thing->email_name_definite_eng,
                ],
            ];
            $thing->save();
        }

        Schema::table('things', function (Blueprint $table) {
            $table->dropColumn('num_items');
            $table->dropColumn('disabled');
            $table->dropColumn('send_reminders');
            $table->dropColumn('email_name_nob');
            $table->dropColumn('email_name_nno');
            $table->dropColumn('email_name_eng');
            $table->dropColumn('email_name_definite_nob');
            $table->dropColumn('email_name_definite_nno');
            $table->dropColumn('email_name_definite_eng');
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
            $table->dropColumn('properties');
            $table->boolean('disabled')->default(false);
            $table->boolean('send_reminders')->default(false);
            $table->string('email_name_nob')->default('Fylles ut');
            $table->string('email_name_nno')->default('Fyllast ut');
            $table->string('email_name_eng')->default('Fill inn');
            $table->string('email_name_definite_nob')->default('Fylles ut');
            $table->string('email_name_definite_nno')->default('Fyllast ut');
            $table->string('email_name_definite_eng')->default('Fill inn');
            $table->integer('num_items')->unsigned()->default(0);
        });
    }
}
