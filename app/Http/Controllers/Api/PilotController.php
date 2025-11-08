<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pilot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PilotController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'height' => ['nullable', 'integer', 'min:0'],
            'birth_year' => ['nullable', 'integer', 'min:0'],
            'gender' => ['nullable', 'string', 'max:50'],
        ]);

        $pilot = Pilot::create($data);

        return response()->json($pilot, 201);
    }

    public function destroy(Pilot $pilot): JsonResponse
    {
        $pilot->delete();

        return response()->json(['message' => 'Piloto eliminado'], 204);
    }
}
