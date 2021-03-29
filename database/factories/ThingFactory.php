<?php

namespace Database\Factories;

use App\Thing;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Thing::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'image' => [
            ],
            'properties' => [
                'name' => [
                    'nob' => $this->faker->words(3, true),
                    'nno' => $this->faker->words(3, true),
                    'eng' => $this->faker->words(3, true),
                ],
                'name_definite' => [
                    'nob' => $this->faker->words(3, true),
                    'nno' => $this->faker->words(3, true),
                    'eng' => $this->faker->words(3, true),
                ],
                'name_indefinite' => [
                    'nob' => $this->faker->words(3, true),
                    'nno' => $this->faker->words(3, true),
                    'eng' => $this->faker->words(3, true),
                ],
            ]
        ];
    }
}
