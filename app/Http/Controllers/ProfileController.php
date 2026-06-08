<?php

namespace App\Http\Controllers;

use App\Actions\UpdateUserProfileAction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __invoke(Request $request, UpdateUserProfileAction $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
        ]);

        $action->execute($request->user(), $validated);

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user'    => $request->user()->fresh()->load('roles')
        ]);
    }
}
