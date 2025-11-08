<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserImageController extends Controller
{
    public function store(Request $request, User $user): JsonResponse
    {
        // Solo el propio usuario o un admin pueden actualizar la imagen
        $authUser = $request->user();

        if ($authUser->id !== $user->id && $authUser->role !== User::ROLE_ADMIN) {
            return response()->json([
                'message' => 'No autorizado a modificar esta imagen',
            ], 403);
        }

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $file = $validated['image'];
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = uniqid('img_') . '_' . Str::slug($originalName) . '.' . $extension;

        // Subimos el archivo a Cloudinary usando el disco configurado
        $uploadedPath = Storage::disk('cloudinary')->putFileAs('users', $file, $filename);
        $url = Storage::disk('cloudinary')->url($uploadedPath);

        $user->forceFill(['image_url' => $url])->save();

        return response()->json([
            'message' => 'Imagen actualizada correctamente',
            'image_url' => $url,
        ]);
    }
}
