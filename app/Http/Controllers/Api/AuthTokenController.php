<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ability;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas',
            ], 422);
        }

        // Recuperamos las habilidades asociadas al rol desde la tabla abilities.
        // Si no hay registro para ese rol, caemos a la habilidad básica "viewer".
        $abilityRecord = Ability::where('role', $user->role)->first();
        $abilities = $abilityRecord?->abilities ?? ['viewer'];

        // Creamos un token personal Sanctum con las habilidades recuperadas.
        $token = $user->createToken('api-token', $abilities);

        return response()->json([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
            'role' => $user->role,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Token revocado',
        ]);
    }
}
