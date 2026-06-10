<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Http\Resources\OrganizationResource;
use App\Http\Requests\{StoreOrganizationRequest, UpdateOrganizationRequest};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

/** Контроллер организаций: CRUD и управление логотипом */
class OrganizationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return static::resourcePolicyMiddleware(Organization::class, 'organization');
    }

    /** Возвращает список организаций с учётом прав доступа текущего пользователя */
    #[OA\Get(
        path: '/organizations',
        summary: 'List organizations (active organizations and own organizations; all for holders of organizations.view)',
        tags: ['Organizations'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of organizations'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Organization::with(['users']);

        if (!$user->can('organizations.view')) {
            $query->where(function ($q) use ($user) {
                $q->where('status', 'active')
                    ->orWhereHas('users', fn ($q2) => $q2->where('users.id', $user->id));
            });
        }

        return $this->apiJson(OrganizationResource::collection($query->latest()->get()));
    }

    /** Возвращает детали одной организации со списком пользователей */
    #[OA\Get(
        path: '/organizations/{organization}',
        summary: 'Get a single organization with its users',
        tags: ['Organizations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'organization', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Organization detail'),
            new OA\Response(response: 403, description: 'Not authorized to view this organization'),
            new OA\Response(response: 404, description: 'Organization not found'),
        ]
    )]
    public function show(Organization $organization): JsonResponse
    {
        return $this->apiJson(new OrganizationResource($organization->load(['users'])));
    }

    /** Создаёт организацию и назначает текущего пользователя её владельцем */
    #[OA\Post(
        path: '/organizations',
        summary: 'Create an organization (current user becomes owner)',
        tags: ['Organizations'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Organization created'),
            new OA\Response(response: 403, description: 'Not authorized to create organizations'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $organization = DB::transaction(function () use ($request) {
            $org = Organization::create($request->validated());
            $org->users()->attach($request->user(), ['role' => 'owner']);
            return $org;
        });

        if ($request->hasFile('logo')) {
            $organization->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        return $this->apiJson(new OrganizationResource($organization->load(['users'])), 201);
    }

    /** Обновляет данные организации и при необходимости заменяет логотип */
    #[OA\Put(
        path: '/organizations/{organization}',
        summary: 'Update an organization (and optionally its logo)',
        tags: ['Organizations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'organization', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Organization updated'),
            new OA\Response(response: 403, description: 'Not authorized to update this organization'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        $organization->update($request->validated());

        if ($request->hasFile('logo')) {
            $organization->clearMediaCollection('logo');
            $organization->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        return $this->apiJson(new OrganizationResource($organization->load(['users'])));
    }

    /** Добавляет пользователя в организацию с указанной ролью */
    #[OA\Post(
        path: '/organizations/{organization}/members',
        summary: 'Add a user to the organization (owner only)',
        tags: ['Organizations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'organization', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Member added'),
            new OA\Response(response: 403, description: 'Not authorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function addMember(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('manageMembers', $organization);

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role'    => 'required|string|in:owner,manager,member',
        ]);

        $organization->users()->syncWithoutDetaching([
            $validated['user_id'] => ['role' => $validated['role']],
        ]);

        return $this->apiJson(new OrganizationResource($organization->load('users')));
    }

    /** Изменяет роль участника в организации */
    #[OA\Put(
        path: '/organizations/{organization}/members/{user}',
        summary: 'Update a member\'s role in the organization (owner only)',
        tags: ['Organizations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'organization', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Role updated'),
            new OA\Response(response: 403, description: 'Not authorized'),
            new OA\Response(response: 404, description: 'Member not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function updateMember(Request $request, Organization $organization, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $organization);

        if (!$organization->users()->where('users.id', $user->id)->exists()) {
            return $this->apiJson(['message' => 'User is not a member of this organization.'], 404);
        }

        $validated = $request->validate([
            'role' => 'required|string|in:owner,manager,member',
        ]);

        $organization->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return $this->apiJson(new OrganizationResource($organization->load('users')));
    }

    /** Удаляет пользователя из организации */
    #[OA\Delete(
        path: '/organizations/{organization}/members/{user}',
        summary: 'Remove a member from the organization (owner only)',
        tags: ['Organizations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'organization', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Member removed'),
            new OA\Response(response: 403, description: 'Not authorized'),
        ]
    )]
    public function removeMember(Request $request, Organization $organization, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $organization);

        // Prevent owner from removing themselves if they're the last owner
        $ownerCount = $organization->users()->wherePivot('role', 'owner')->count();
        if ($ownerCount === 1 && $user->id === $request->user()->id) {
            return $this->apiJson(['message' => 'Cannot remove the last owner.'], 422);
        }

        $organization->users()->detach($user->id);

        return $this->apiJson(['message' => 'Member removed successfully.']);
    }

    /** Удаляет организацию */
    #[OA\Delete(
        path: '/organizations/{organization}',
        summary: 'Delete an organization',
        tags: ['Organizations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'organization', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Organization deleted'),
            new OA\Response(response: 403, description: 'Not authorized to delete this organization'),
        ]
    )]
    public function destroy(Organization $organization): JsonResponse
    {
        $organization->delete();
        return $this->apiJson(null, 204);
    }
}
