<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\UserIdentifier::class, function (Faker $faker) {
    $faker->addProvider(new \Tests\Faker\Library($faker));
    return [
        'type' => 'barcode',
        'value' => $faker->userBarcode,
    ];
});
