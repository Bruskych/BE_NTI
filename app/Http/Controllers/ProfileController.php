<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Обновление личных данных профиля (Имя и Фамилия).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:2|max:255',
        ]);

        $user = $request->user();

        $user->updateProfileData($request->input('name'));

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user'    => $user->load('roles')
        ]);
    }
}
