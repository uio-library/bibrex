<?php

use Illuminate\Support\Facades\Schema;
use App\Database\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameDokid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->renameColumn('dokid', 'barcode');
            $table->dropColumn(['knyttid', 'objektid', 'title', 'subtitle', 'authors', 'year', 'cover_image']);
            $table->jsonb('properties')->default('{}');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('properties');

            $table->renameColumn('barcode', 'dokid');

            $table->string('knyttid')->unique()->nullable();
            $table->string('objektid')->nullable();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('authors')->nullable();
            $table->string('year')->nullable();
            $table->string('cover_image')->nullable();
        });
    }
}
