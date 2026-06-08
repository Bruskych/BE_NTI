<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

use App\Actions\UpdateUserProfileAction;

class ProfileController extends Controller
{
    protected UpdateUserProfileAction $action;

    public function __construct(UpdateUserProfileAction $action)
    {
        $this->action = $action;
    }

    /**
     * 1. Обновление имени + фамилии
     */
    public function updateName(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
        ]);
        $this->action->execute($request->user(), $validated);
        return $this->respondWithUser($request->user(), 'Profile updated successfully!');
    }

    /**
     * 2. Обновление Email
     */
    public function updateEmail(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);
        $this->action->execute($user, $validated);
        return $this->respondWithUser($user, 'Email updated successfully!');
    }

    /**
     * 3. Обновление аватарки
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
        $this->action->execute($request->user(), [
            'avatar' => $request->file('avatar')
        ]);
        return $this->respondWithUser($request->user(), 'Avatar updated successfully!');
    }

    /**
     * Вспомогательный метод для унификации ответа
     */
    private function respondWithUser($user, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'user'    => $user->fresh()->load('roles')
        ]);
    }
}
