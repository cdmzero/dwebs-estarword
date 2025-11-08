<?php

namespace Tests\Unit;

use App\Models\Maintenance;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    public function test_calculate_duration_cost_for_completed_maintenance(): void
    {
        $maintenance = new Maintenance([
            'date_planned' => '2025-01-01 08:00:00',
            'date_completed' => '2025-01-05 08:00:00',
        ]);

        $this->assertEquals(400, $maintenance->calculateDurationCost());
    }

    public function test_calculate_duration_cost_returns_zero_when_not_completed(): void
    {
        $maintenance = new Maintenance([
            'date_planned' => '2025-01-01 08:00:00',
            'date_completed' => null,
        ]);

        $this->assertEquals(0, $maintenance->calculateDurationCost());
    }

    public function test_calculate_duration_cost_returns_zero_when_completion_before_start(): void
    {
        $maintenance = new Maintenance([
            'date_planned' => '2025-01-05 08:00:00',
            'date_completed' => '2025-01-04 08:00:00',
        ]);

        $this->assertEquals(0, $maintenance->calculateDurationCost());
    }
}
