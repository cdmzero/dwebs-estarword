<?php

namespace Database\Factories;

use App\Models\Maintenance;
use App\Models\Spaceship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Maintenance>
 */
class MaintenanceFactory extends Factory
{
    protected $model = Maintenance::class;

    public function definition(): array
    {
        $completed = fake()->boolean(70);

        return [
            'spaceship_id' => Spaceship::factory(),
            'date_completed' => $completed ? fake()->dateTimeBetween('-12 months', '-1 week') : null,
            'date_planned' => fake()->dateTimeBetween('now', '+3 months'),
            'description' => fake()->sentence(8),
            'cost' => fake()->randomFloat(2, 500, 25000),
        ];
    }
}




