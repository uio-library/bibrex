<?php

use Illuminate\Support\Facades\Schema;
use App\Database\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $conn = \DB::connection()->getName();
        $locale = config('database.connections.' . $conn . '.locale');

        if ($conn == 'pgsql' && !empty($locale)) {
            \DB::statement("alter table users alter column lastname type text collate \"${locale}\"");
            \DB::statement("alter table users alter column firstname type text collate \"${locale}\"");
            \DB::statement("alter table things alter column name type text collate \"${locale}\"");
            \DB::statement("alter table libraries alter column name type text collate \"${locale}\"");
            \DB::statement("alter table libraries alter column name_eng type text collate \"en_US\"");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->index('lastname');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_lastname_index');
        });

        if (\DB::connection()->getName() == 'pgsql') {
            \DB::statement('alter table users alter column lastname type character varying(255)');
            \DB::statement('alter table users alter column firstname type character varying(255)');
            \DB::statement('alter table things alter column name type character varying(255)');
            \DB::statement('alter table libraries alter column name type character varying(255)');
            \DB::statement('alter table libraries alter column name_eng type character varying(255)');
        }
    }
}
