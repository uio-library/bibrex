<?php

use App\User;
use App\UserIdentifier;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserIdentifiers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_identifiers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->passthru('citext', 'value')->unique();
            $table->string('type');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });

        foreach (User::get() as $user) {
            if ($user->barcode) {
                $user->identifiers()->create([
                    'value' => $user->barcode,
                    'type' => 'barcode',
                ]);
            }
            if ($user->university_id) {
                $user->identifiers()->create([
                    'value' => $user->university_id,
                    'type' => 'university_id',
                ]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('barcode');
            $table->dropColumn('university_id');
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
            $table->passthru('citext', 'barcode')->unique()->nullable();
            $table->passthru('citext', 'university_id')->unique()->nullable();
        });

        foreach (UserIdentifier::get() as $userIdentifier) {
            $user = $userIdentifier->user;
            if ($userIdentifier->type == 'barcode') {
                $user->barcode = $userIdentifier->value;
            }
            if ($userIdentifier->type == 'university_id') {
                $user->university_id = $userIdentifier->value;
            }
            $user->save();
        }

        Schema::dropIfExists('user_identifiers');
    }
}
