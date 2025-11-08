<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pilot;
use App\Models\Spaceship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpaceshipPilotController extends Controller
{
    public function store(Request $request, Spaceship $nave): JsonResponse
    {
        $data = $request->validate([
            'pilot_id' => ['required', 'exists:pilots,id'],
            'assigned_date' => ['required', 'date'],
            'exit_date' => ['nullable', 'date', 'after_or_equal:assigned_date'],
        ]);

        $nave->pilots()->syncWithoutDetaching([
            $data['pilot_id'] => [
                'assigned_date' => $data['assigned_date'],
                'exit_date' => $data['exit_date'] ?? null,
            ],
        ]);

        return response()->json([
            'message' => 'Piloto asignado correctamente',
        ], 201);
    }
}
