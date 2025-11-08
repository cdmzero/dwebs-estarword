<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Pilot;
use App\Models\Planet;
use App\Models\Spaceship;
use App\Models\SpaceshipPilot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListsController extends Controller
{
    public function index(): JsonResponse
    {
        $data = [];
        $data['planets'] = Planet::select('id', 'name', 'rotation_time', 'population', 'climate')->get();
        $data['spaceships'] = Spaceship::select('id', 'planet_id', 'name', 'model', 'crew', 'passengers', 'type')->get();
        $data['maintenances'] = Maintenance::select('id', 'spaceship_id', 'date_completed', 'date_planned', 'description', 'cost')->get();
        $data['pilots'] = Pilot::select('id', 'name', 'height', 'birth_year', 'gender')->get();
        $data['spaceship_pilots'] = SpaceshipPilot::select('id', 'spaceship_id', 'pilot_id', 'assigned_date', 'exit_date')->get();

        return response()->json($data);
    }

    public function show(string $list, int $id): JsonResponse
    {
        $model = null;

        if ($list === 'planets') {
            $model = Planet::select('id', 'name', 'rotation_time', 'population', 'climate')->find($id);
        } elseif ($list === 'spaceships') {
            $model = Spaceship::select('id', 'planet_id', 'name', 'model', 'crew', 'passengers', 'type')->find($id);
        } elseif ($list === 'maintenances') {
            $model = Maintenance::select('id', 'spaceship_id', 'date_completed', 'date_planned', 'description', 'cost')->find($id);
        } elseif ($list === 'pilots') {
            $model = Pilot::select('id', 'name', 'height', 'birth_year', 'gender')->find($id);
        } elseif ($list === 'spaceship_pilots') {
            $model = SpaceshipPilot::select('id', 'spaceship_id', 'pilot_id', 'assigned_date', 'exit_date')->find($id);
        }

        if (!$model) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        return response()->json($model);
    }

    public function listAll(string $list): JsonResponse
    {
        $items = [];

        if ($list === 'planets') {
            $items = Planet::select('id', 'name', 'rotation_time', 'population', 'climate')->get();
        } elseif ($list === 'spaceships') {
            $items = Spaceship::select('id', 'planet_id', 'name', 'model', 'crew', 'passengers', 'type')->get();
        } elseif ($list === 'maintenances') {
            $items = Maintenance::select('id', 'spaceship_id', 'date_completed', 'date_planned', 'description', 'cost')->get();
        } elseif ($list === 'pilots') {
            $items = Pilot::select('id', 'name', 'height', 'birth_year', 'gender')->get();
        } elseif ($list === 'spaceship_pilots') {
            $items = SpaceshipPilot::select('id', 'spaceship_id', 'pilot_id', 'assigned_date', 'exit_date')->get();
        } else {
            return response()->json(['message' => 'Lista no soportada'], 404);
        }

        return response()->json($items);
    }

    public function spaceshipsWithoutPilot(): JsonResponse
    {
        $spaceships = Spaceship::select('id', 'planet_id', 'name', 'model', 'crew', 'passengers', 'type')
            ->whereDoesntHave('pilots')
            ->get();

        return response()->json($spaceships);
    }

    public function pilotAssignments(): JsonResponse
    {
        $assignments = SpaceshipPilot::with([
            'pilot:id,name',
            'spaceship:id,name',
        ])->select('id', 'spaceship_id', 'pilot_id', 'assigned_date', 'exit_date')->get();

        return response()->json($assignments);
    }

    public function currentActivePilots(): JsonResponse
    {
        $currentAssignments = SpaceshipPilot::with([
            'pilot:id,name',
            'spaceship:id,name',
        ])->select('id', 'spaceship_id', 'pilot_id', 'assigned_date', 'exit_date')
            ->whereNull('exit_date')
            ->get()
            ->map(function ($assignment) {
                return [
                    'pilot_id' => $assignment->pilot_id,
                    'pilot_name' => optional($assignment->pilot)->name,
                    'spaceship_id' => $assignment->spaceship_id,
                    'spaceship_name' => optional($assignment->spaceship)->name,
                    'assigned_date' => $assignment->assigned_date,
                ];
            });

        return response()->json($currentAssignments);
    }

    public function maintenancesBetweenDates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
        ]);

        $maintenances = Maintenance::select('id', 'spaceship_id', 'date_completed', 'date_planned', 'description', 'cost')
            ->whereBetween('date_planned', [$validated['start'], $validated['end']])
            ->with('spaceship:id,name')
            ->orderBy('date_planned')
            ->get()
            ->map(function ($maintenance) {
                return [
                    'spaceship_id' => $maintenance->spaceship_id,
                    'spaceship_name' => optional($maintenance->spaceship)->name,
                    'maintenance_id' => $maintenance->id,
                    'date_planned' => $maintenance->date_planned,
                    'date_completed' => $maintenance->date_completed,
                    'description' => $maintenance->description,
                    'cost' => $maintenance->cost,
                ];
            });

        return response()->json($maintenances);
    }
}

