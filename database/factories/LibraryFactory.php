<?php

use Faker\Generator as Faker;

$factory->define(App\Library::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'name_eng' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => Hash::make('secret'), // '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});
