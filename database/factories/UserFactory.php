<?php

namespace Database\Factories;

use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // $faker->addProvider(new \Tests\Faker\Library($faker));
        return [
            'lastname' => $this->faker->lastName,
            'firstname' => $this->faker->firstName,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'in_alma' => true,
            'alma_primary_id' => $this->faker->email,
            'alma_user_group' => $this->faker->word,
        ];
    }
}
