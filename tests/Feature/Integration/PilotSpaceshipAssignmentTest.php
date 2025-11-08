<?php

namespace Tests\Feature\Integration;

use App\Models\Pilot;
use App\Models\Planet;
use App\Models\Spaceship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PilotSpaceshipAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--seed' => true]);
    }

    public function test_full_flow_register_pilot_spaceship_and_assign(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        // Registrar piloto
        $pilotResponse = $this->actingAs($admin)->postJson('/api/pilots', [
            'name' => 'Integration Pilot',
            'height' => 180,
            'birth_year' => 25,
            'gender' => 'male',
        ]);
        $pilotResponse->assertCreated();

        $pilotId = $pilotResponse->json('id');
        $this->assertDatabaseHas('pilots', ['id' => $pilotId, 'name' => 'Integration Pilot']);

        // Registrar nave
        $planet = Planet::first();
        $shipResponse = $this->postJson('/api/naves', [
            'planet_id' => $planet->id,
            'name' => 'Integration Ship',
            'model' => 'Model-Z',
            'crew' => 4,
            'passengers' => 12,
            'type' => 'cargo',
        ]);
        $shipResponse->assertCreated();

        $shipId = $shipResponse->json('id');
        $this->assertDatabaseHas('spaceships', ['id' => $shipId, 'name' => 'Integration Ship']);

        // Asignar piloto a nave
        $assignmentResponse = $this->postJson("/api/naves/{$shipId}/pilots", [
            'pilot_id' => $pilotId,
            'assigned_date' => '2025-11-08',
        ]);
        $assignmentResponse->assertCreated();

        $this->assertDatabaseHas('spaceship_pilots', [
            'spaceship_id' => $shipId,
            'pilot_id' => $pilotId,
        ]);
    }
}
