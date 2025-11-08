<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spaceship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpaceshipController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Spaceship::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'planet_id' => ['required', 'integer', 'exists:planets,id'],
            'name' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'crew' => ['nullable', 'integer', 'min:0'],
            'passengers' => ['nullable', 'integer', 'min:0'],
            'type' => ['nullable', 'string', 'max:255'],
        ]);

        $spaceship = Spaceship::create($validated);

        return response()->json($spaceship, 201);
    }

    public function show(Spaceship $nave): JsonResponse
    {
        return response()->json($nave);
    }

    public function update(Request $request, Spaceship $nave): JsonResponse
    {
        $validated = $request->validate([
            'planet_id' => ['sometimes', 'integer', 'exists:planets,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'model' => ['sometimes', 'nullable', 'string', 'max:255'],
            'crew' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'passengers' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'type' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $nave->update($validated);

        return response()->json($nave);
    }

    public function destroy(Spaceship $nave): JsonResponse
    {
        $nave->delete();

        return response()->json(null, 204);
    }

    public function storeMaintenance(Request $request, Spaceship $nave): JsonResponse
    {
        $validated = $request->validate([
            'date_completed' => ['nullable', 'date'],
            'date_planned' => ['required', 'date'],
            'description' => ['required', 'string'],
            'cost' => ['required', 'numeric', 'min:0'],
        ]);

        $maintenance = $nave->maintenances()->create($validated);

        return response()->json($maintenance, 201);
    }

    public function listMaintenances(Spaceship $nave): JsonResponse
    {
        $maintenances = $nave->maintenances()
            ->select('id', 'spaceship_id', 'date_completed', 'date_planned', 'description', 'cost', 'created_at', 'updated_at')
            ->orderByDesc('date_planned')
            ->get();

        return response()->json($maintenances);
    }
}

