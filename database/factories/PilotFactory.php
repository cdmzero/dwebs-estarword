<?php

namespace Database\Factories;

use App\Models\Pilot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pilot>
 */
class PilotFactory extends Factory
{
    protected $model = Pilot::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name(),
            'height' => fake()->numberBetween(150, 210),
            'birth_year' => fake()->numberBetween(10, 90),
            'gender' => fake()->randomElement(['male', 'female', 'non-binary']),
        ];
    }
}




