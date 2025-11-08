<?php

namespace Tests\Feature;

use App\Models\Maintenance;
use App\Models\Pilot;
use App\Models\Planet;
use App\Models\Spaceship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--seed' => true]);
    }

    public function test_admin_can_register_spaceship(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $planet = Planet::first();

        $response = $this->actingAs($admin)->postJson('/api/naves', [
            'planet_id' => $planet->id,
            'name' => 'Test Ship ' . Str::uuid(),
            'model' => 'Model-X',
            'crew' => 5,
            'passengers' => 20,
            'type' => 'exploration',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('spaceships', ['name' => $response->json('name')]);
    }

    public function test_admin_can_delete_pilot(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $pilot = Pilot::first();

        $response = $this->actingAs($admin)->deleteJson("/api/pilots/{$pilot->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('pilots', ['id' => $pilot->id]);
    }

    public function test_manager_can_update_maintenance(): void
    {
        $manager = User::where('email', 'manager@example.com')->first();
        $maintenance = Maintenance::first();

        $response = $this->actingAs($manager)->patchJson("/api/naves/{$maintenance->spaceship_id}/maintenances/{$maintenance->id}", [
            'description' => 'Descripción actualizada desde test',
        ]);

        $response->assertOk();
        $this->assertEquals('Descripción actualizada desde test', $maintenance->fresh()->description);
    }
}
