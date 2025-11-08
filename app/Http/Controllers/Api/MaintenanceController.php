<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function update(Request $request, $nave, Maintenance $maintenance): JsonResponse
    {
        $data = $request->validate([
            'description' => ['sometimes', 'string', 'max:255'],
            'date_completed' => ['sometimes', 'nullable', 'date'],
            'cost' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $maintenance->fill($data);
        $maintenance->save();

        return response()->json($maintenance);
    }
}
