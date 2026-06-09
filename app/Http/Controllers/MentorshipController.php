<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMentorshipRequest;
use App\Http\Requests\UpdateMentorshipRequest;
use App\Http\Resources\MentorshipResource;
use App\Models\Mentorship;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/** Контроллер менторства: назначение менторов проектам и управление связями */
class MentorshipController extends Controller
{
    /** Возвращает список всех менторств с проектом и ментором */
    #[OA\Get(
        path: '/mentorships',
        summary: 'List mentorships with project and mentor',
        tags: ['Mentorships'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of mentorships'),
            new OA\Response(response: 403, description: 'Not authorized to view mentorships'),
        ]
    )]
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Mentorship::class);
        return $this->apiJson(MentorshipResource::collection(Mentorship::with(['project', 'mentor'])->get()));
    }

    /** Создаёт новое менторство, назначая ментора проекту */
    #[OA\Post(
        path: '/mentorships',
        summary: '[Admin] Assign a mentor to a project',
        tags: ['Mentorships'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Mentorship created'),
            new OA\Response(response: 403, description: 'Not authorized to create mentorships'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreMentorshipRequest $request): JsonResponse
    {
        $this->authorize('create', Mentorship::class);
        $mentorship = Mentorship::create($request->validated());
        return $this->apiJson(new MentorshipResource($mentorship), 201);
    }

    /** Возвращает детали одного менторства с консультациями */
    #[OA\Get(
        path: '/mentorships/{mentorship}',
        summary: 'Get a single mentorship with project, mentor and consultations',
        tags: ['Mentorships'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'mentorship', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Mentorship detail'),
            new OA\Response(response: 403, description: 'Not authorized to view this mentorship'),
            new OA\Response(response: 404, description: 'Mentorship not found'),
        ]
    )]
    public function show(Mentorship $mentorship): JsonResponse
    {
        $this->authorize('view', $mentorship);
        return $this->apiJson(new MentorshipResource($mentorship->load(['project', 'mentor', 'consultations'])));
    }

    /** Обновляет данные менторства */
    #[OA\Put(
        path: '/mentorships/{mentorship}',
        summary: 'Update a mentorship',
        tags: ['Mentorships'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'mentorship', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Mentorship updated'),
            new OA\Response(response: 403, description: 'Not authorized to update this mentorship'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateMentorshipRequest $request, Mentorship $mentorship): JsonResponse
    {
        $this->authorize('update', $mentorship);
        $mentorship->update($request->validated());
        return $this->apiJson(new MentorshipResource($mentorship));
    }

    /** Удаляет менторство */
    #[OA\Delete(
        path: '/mentorships/{mentorship}',
        summary: 'Delete a mentorship',
        tags: ['Mentorships'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'mentorship', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Mentorship deleted'),
            new OA\Response(response: 403, description: 'Not authorized to delete this mentorship'),
        ]
    )]
    public function destroy(Mentorship $mentorship): JsonResponse
    {
        $this->authorize('delete', $mentorship);
        $mentorship->delete();
        return $this->apiJson(['message' => 'Mentorship deleted successfully']);
    }
}
