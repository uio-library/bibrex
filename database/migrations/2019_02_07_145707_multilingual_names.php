<?php

use Illuminate\Support\Facades\Schema;
use App\Database\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MultilingualNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (\DB::table('things')->get() as $row) {
            $properties = json_decode($row->properties);
            $properties->name = [
                'nob' => $row->name,
                'nno' => $row->name,
                'eng' => $row->name,
            ];
            \DB::table('things')->where('id', $row->id)->update([
                'properties' => json_encode($properties),
            ]);
        }

        Schema::table('things', function (Blueprint $table) {
            $table->dropColumn('name');
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
            $table->text('name')->nullable();
        });

        foreach (\DB::table('things')->get() as $row) {
            $properties = json_decode($row->properties);
            \DB::table('things')->where('id', $row->id)->update([
                'name' => $properties->name->nob,
            ]);
            unset($properties->name);
            \DB::table('things')->where('id', $row->id)->update([
                'properties' => json_encode($properties),
            ]);
        }
    }
}
