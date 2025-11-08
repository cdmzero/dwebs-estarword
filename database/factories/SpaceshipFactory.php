<?php

namespace Database\Factories;

use App\Models\Planet;
use App\Models\Spaceship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Spaceship>
 */
class SpaceshipFactory extends Factory
{
    protected $model = Spaceship::class;

    public function definition(): array
    {
        $types = [
            'freighter',
            'starfighter',
            'diplomatic transport',
            'bomber',
            'interceptor',
            'cargo',
            'exploration',
        ];

        return [
            'planet_id' => Planet::factory(),
            'name' => fake()->unique()->lexify('????-') . fake()->unique()->numerify('###'),
            'model' => fake()->bothify('Model-##??'),
            'crew' => fake()->numberBetween(1, 50),
            'passengers' => fake()->numberBetween(0, 200),
            'type' => fake()->randomElement($types),
        ];
    }
}




