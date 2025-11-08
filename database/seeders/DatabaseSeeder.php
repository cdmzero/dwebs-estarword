<?php

namespace Database\Seeders;

use App\Models\Ability;
use App\Models\Maintenance;
use App\Models\Pilot;
use App\Models\Planet;
use App\Models\Spaceship;
use App\Models\SpaceshipPilot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Mapa de habilidades por rol
        Ability::query()->upsert([
            ['role' => User::ROLE_ADMIN, 'abilities' => json_encode(['*']), 'created_at' => now(), 'updated_at' => now()],
            ['role' => User::ROLE_MANAGER, 'abilities' => json_encode(['maintainer', 'viewer']), 'created_at' => now(), 'updated_at' => now()],
            ['role' => User::ROLE_USER, 'abilities' => json_encode(['viewer']), 'created_at' => now(), 'updated_at' => now()],
        ], ['role'], ['abilities', 'updated_at']);

        // Usuarios de ejemplo
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'role' => User::ROLE_ADMIN, 'password' => bcrypt('password')]
        );

        User::firstOrCreate(
            ['email' => 'manager@example.com'],
            ['name' => 'Manager User', 'role' => User::ROLE_MANAGER, 'password' => bcrypt('password')]
        );

        User::firstOrCreate(
            ['email' => 'user@example.com'],
            ['name' => 'Regular User', 'role' => User::ROLE_USER, 'password' => bcrypt('password')]
        );

        // Planetas
        $tatooine = Planet::firstOrCreate([
            'name' => 'Tatooine',
        ], [
            'rotation_time' => 23,
            'population' => 200000,
            'climate' => 'desert',
        ]);

        $naboo = Planet::firstOrCreate([
            'name' => 'Naboo',
        ], [
            'rotation_time' => 26,
            'population' => 4500000000,
            'climate' => 'temperate',
        ]);

        $coruscant = Planet::firstOrCreate([
            'name' => 'Coruscant',
        ], [
            'rotation_time' => 24,
            'population' => 1000000000000,
            'climate' => 'urban',
        ]);

        // Naves
        $falcon = Spaceship::firstOrCreate([
            'name' => 'Millennium Falcon',
        ], [
            'planet_id' => $tatooine->id,
            'model' => 'YT-1300f',
            'crew' => 4,
            'passengers' => 6,
            'type' => 'cargo',
        ]);

        $xwing = Spaceship::firstOrCreate([
            'name' => 'Red Five',
        ], [
            'planet_id' => $naboo->id,
            'model' => 'T-65 X-Wing',
            'crew' => 1,
            'passengers' => 0,
            'type' => 'starfighter',
        ]);

        $starDestroyer = Spaceship::firstOrCreate([
            'name' => 'Devastator',
        ], [
            'planet_id' => $coruscant->id,
            'model' => 'Imperial I-class',
            'crew' => 37000,
            'passengers' => 9500,
            'type' => 'destroyer',
        ]);

        // Pilotos
        $hanSolo = Pilot::firstOrCreate([
            'name' => 'Han Solo',
        ], [
            'height' => 180,
            'birth_year' => 29,
            'gender' => 'male',
            'image_url' => url('images/default_pilot.png'),
        ]);

        $luke = Pilot::firstOrCreate([
            'name' => 'Luke Skywalker',
        ], [
            'height' => 172,
            'birth_year' => 19,
            'gender' => 'male',
            'image_url' => url('images/default_pilot.png'),
        ]);

        $leia = Pilot::firstOrCreate([
            'name' => 'Leia Organa',
        ], [
            'height' => 150,
            'birth_year' => 19,
            'gender' => 'female',
            'image_url' => url('images/default_pilot.png'),
        ]);

        // Mantenimientos
        Maintenance::create([
            'spaceship_id' => $falcon->id,
            'date_planned' => '2025-12-01 10:00:00',
            'date_completed' => null,
            'description' => 'Revisión completa de hiperimpulsor',
            'cost' => 1500.00,
        ]);

        Maintenance::create([
            'spaceship_id' => $xwing->id,
            'date_planned' => '2025-11-15 09:30:00',
            'date_completed' => '2025-10-20 14:00:00',
            'description' => 'Cambio de cañones láser',
            'cost' => 900.00,
        ]);

        Maintenance::create([
            'spaceship_id' => $starDestroyer->id,
            'date_planned' => '2026-01-10 08:00:00',
            'date_completed' => null,
            'description' => 'Mantenimiento de reactores principales',
            'cost' => 50000.00,
        ]);

        // Asignaciones piloto-nave
        $falcon->pilots()->syncWithoutDetaching([
            $hanSolo->id => [
                'assigned_date' => '2025-05-01',
                'exit_date' => null,
            ],
        ]);

        $xwing->pilots()->syncWithoutDetaching([
            $luke->id => [
                'assigned_date' => '2025-06-10',
                'exit_date' => null,
            ],
        ]);

        $falcon->pilots()->syncWithoutDetaching([
            $leia->id => [
                'assigned_date' => '2025-08-20',
                'exit_date' => '2025-10-01',
            ],
        ]);

        $this->generateDemoTokens();
    }

    private function generateDemoTokens(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $manager = User::where('email', 'manager@example.com')->first();
        $user = User::where('email', 'user@example.com')->first();

        if (! $admin || ! $manager || ! $user) {
            return;
        }

        $this->deleteExistingDemoTokens($admin, 'demo-admin');
        $this->deleteExistingDemoTokens($manager, 'demo-manager');
        $this->deleteExistingDemoTokens($user, 'demo-user');

        $tokens = [
            'admin' => $admin->createToken('demo-admin', ['*'])->plainTextToken,
            'manager' => $manager->createToken('demo-manager', ['maintainer', 'viewer'])->plainTextToken,
            'user' => $user->createToken('demo-user', ['viewer'])->plainTextToken,
        ];

        Storage::disk('local')->put('demo_tokens.json', json_encode($tokens, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function deleteExistingDemoTokens(User $user, string $name): void
    {
        $user->tokens()->where('name', $name)->delete();
    }
}
