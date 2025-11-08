<?php

namespace Database\Factories;

use App\Models\Planet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Planet>
 */
class PlanetFactory extends Factory
{
    protected $model = Planet::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'rotation_time' => fake()->numberBetween(10, 48),
            'population' => fake()->numberBetween(0, 1000000000000),
            'climate' => fake()->randomElement([
                'arid',
                'temperate',
                'frozen',
                'tropical',
                'humid',
                'desert',
                'stormy',
            ]),
        ];
    }
}




