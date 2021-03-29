<?php

namespace Database\Factories;

use App\UserIdentifier;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserIdentifierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserIdentifier::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $this->faker->addProvider(new \Tests\Faker\Library($this->faker));

        return [
            'type' => 'barcode',
            'value' => $this->faker->userBarcode,
        ];
    }
}
