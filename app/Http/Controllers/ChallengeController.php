<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Http\Resources\ChallengeResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreChallengeRequest;
use App\Http\Requests\UpdateChallengeRequest;
use OpenApi\Attributes as OA;

/** Контроллер задач от компаний (challenges): CRUD и управление специализациями */
class ChallengeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Challenge::class, 'challenge');
    }

    /** Возвращает список задач, доступных текущему пользователю */
    #[OA\Get(
        path: '/challenges',
        summary: 'List challenges visible to the current user',
        tags: ['Challenges'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of challenges'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $challenges = Challenge::with(['program', 'organization', 'specializations'])
            ->withCount('applications')
            ->visibleTo($request->user())
            ->latest()
            ->get();

        return $this->apiJson(ChallengeResource::collection($challenges));
    }

    /** Создаёт новую задачу от компании, при необходимости связывая специализации */
    #[OA\Post(
        path: '/challenges',
        summary: '[Company/Admin] Create a challenge, optionally linking specializations',
        tags: ['Challenges'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Challenge created'),
            new OA\Response(response: 403, description: 'Not authorized to create challenges'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreChallengeRequest $request): JsonResponse
    {
        $challenge = Challenge::create($request->validated());

        if ($request->has('specialization_ids')) {
            $challenge->specializations()->sync($request->specialization_ids);
        }

        return $this->apiJson(new ChallengeResource($challenge), 201);
    }

    /** Возвращает детали одной задачи от компании */
    #[OA\Get(
        path: '/challenges/{challenge}',
        summary: 'Get a single challenge with program, organization, specializations and product owner',
        tags: ['Challenges'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'challenge', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Challenge detail'),
            new OA\Response(response: 403, description: 'Not authorized to view this challenge'),
            new OA\Response(response: 404, description: 'Challenge not found'),
        ]
    )]
    public function show(Challenge $challenge): JsonResponse
    {
        $challenge->load(['program', 'organization', 'specializations', 'productOwner']);

        return $this->apiJson(new ChallengeResource($challenge));
    }

    /** Обновляет задачу от компании и синхронизирует специализации */
    #[OA\Put(
        path: '/challenges/{challenge}',
        summary: 'Update a challenge, optionally re-syncing specializations',
        tags: ['Challenges'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'challenge', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Challenge updated'),
            new OA\Response(response: 403, description: 'Not authorized to update this challenge'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateChallengeRequest $request, Challenge $challenge): JsonResponse
    {
        $challenge->update($request->validated());

        if ($request->has('specialization_ids')) {
            $challenge->specializations()->sync($request->specialization_ids);
        }

        return $this->apiJson(new ChallengeResource($challenge->load(['program', 'organization', 'specializations'])));
    }

    /** Удаляет задачу от компании */
    #[OA\Delete(
        path: '/challenges/{challenge}',
        summary: 'Delete a challenge',
        tags: ['Challenges'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'challenge', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Challenge deleted'),
            new OA\Response(response: 403, description: 'Not authorized to delete this challenge'),
        ]
    )]
    public function destroy(Challenge $challenge): JsonResponse
    {
        $challenge->delete();

        return $this->apiJson(['message' => 'Challenge deleted successfully']);
    }
}
