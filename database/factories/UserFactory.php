<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'barcode' => $faker->ean13,
        'lastname' => $faker->lastName,
        'firstname' => $faker->firstName,
        'phone' => $faker->phoneNumber,
        'email' => $faker->email,
        'in_alma' => true,
        'alma_primary_id' => $faker->email,
        'alma_user_group' => $faker->word,
    ];
});
