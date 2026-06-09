<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

use App\Actions\UpdateUserProfileAction;
use App\Models\StudentProfile;
use OpenApi\Attributes as OA;

/** Контроллер профиля пользователя: обновление имени, email и аватара */
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
    #[OA\Post(
        path: '/settings/update-profile/name',
        summary: 'Update the current user\'s display name',
        tags: ['Profile'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Profile updated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
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
    #[OA\Post(
        path: '/settings/update-profile/email',
        summary: 'Update the current user\'s email address',
        tags: ['Profile'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Email updated'),
            new OA\Response(response: 422, description: 'Validation error (e.g. email already taken)'),
        ]
    )]
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
    #[OA\Post(
        path: '/settings/update-profile/avatar',
        summary: 'Upload/replace the current user\'s avatar image',
        tags: ['Profile'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Avatar updated'),
            new OA\Response(response: 422, description: 'Validation error (invalid image)'),
        ]
    )]
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

    #[OA\Get(
        path: '/settings/student-profile',
        summary: 'Get the current student\'s onboarding profile',
        tags: ['Profile'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Student profile (null if not yet filled)'),
        ]
    )]
    public function showStudentProfile(Request $request): JsonResponse
    {
        $profile = StudentProfile::firstOrNew(['user_id' => $request->user()->id]);
        return $this->apiJson(['data' => $profile->exists ? $profile : null]);
    }

    #[OA\Put(
        path: '/settings/student-profile',
        summary: 'Create or update the current student\'s onboarding profile',
        tags: ['Profile'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Profile saved'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateStudentProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'study_program'    => 'required|string|max:255',
            'year'             => 'required|integer|min:1|max:6',
            'skills_json'      => 'nullable|array',
            'skills_json.*'    => 'string|max:100',
            'avg_grade'        => 'nullable|numeric|min:1|max:4',
            'has_carried_subjects' => 'nullable|boolean',
        ]);

        $profile = StudentProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return $this->apiJson(['data' => $profile]);
    }

    /**
     * Вспомогательный метод для унификации ответа
     */
    private function respondWithUser($user, string $message): JsonResponse
    {
        return $this->apiJson([
            'message' => $message,
            'user'    => $user->fresh()->load('roles')
        ]);
    }
}
