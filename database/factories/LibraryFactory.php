<?php

namespace Database\Factories;

use App\Library;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class LibraryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Library::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'name_eng' => $this->faker->company,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('secret'),
            'remember_token' => str_random(10),
        ];
    }
}
