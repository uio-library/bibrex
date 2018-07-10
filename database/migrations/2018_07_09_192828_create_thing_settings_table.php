<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class CreateThingSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $settings=[];

        foreach (\DB::table('library_thing')->get() as $row) {
            $settings[$row->thing_id][$row->library_id]['loans_without_barcode'] = (bool) !$row->require_item;
            $settings[$row->thing_id][$row->library_id]['reminders'] = (bool) $row->send_reminders;
        }

        $thingSettingsData = [];
        foreach ($settings as $thing_id => $x) {
            foreach ($x as $lib_id => $data) {
                $thingSettingsData[] = [
                    'thing_id' => $thing_id,
                    'library_id' => $lib_id,
                    'data' => json_encode($data),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        foreach (\DB::table('things')->get() as $row) {
            $properties = json_decode($row->properties);
            $properties->loan_time = $row->loan_time;
            \DB::table('things')->where('id', $row->id)->update([
                'properties' => json_encode($properties),
            ]);
        }

        Schema::create('thing_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('library_id')->unsigned();
            $table->integer('thing_id')->unsigned();
            $table->jsonb('data');
            $table->timestamps();

            $table->unique(['library_id', 'thing_id']);

            $table->foreign('library_id')
                ->references('id')->on('libraries')
                ->onDelete('cascade');

            $table->foreign('thing_id')
                ->references('id')->on('things')
                ->onDelete('cascade');
        });

        \DB::table('thing_settings')->insert($thingSettingsData);

        Schema::dropIfExists('library_thing');

        Schema::table('things', function (Blueprint $table) {
            $table->dropColumn('loan_time');
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
            $table->integer('loan_time')->unsigned()->default(1);
        });

        Schema::dropIfExists('thing_settings');
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
}
