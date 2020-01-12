<?php

use Illuminate\Support\Facades\Schema;
use App\Database\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLogContext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::unprepared("
            ALTER TABLE log
            ALTER COLUMN context
            TYPE jsonb
            USING context::jsonb
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::unprepared("
            ALTER TABLE log
            ALTER COLUMN context
            TYPE text
            USING context::text
        ");
    }
}
