<?php

namespace App\Http\Controllers;

use App\Models\{Milestone, Project};
use App\Http\Resources\MilestoneResource;
use App\Http\Requests\{StoreMilestoneRequest, UpdateMilestoneRequest};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use OpenApi\Attributes as OA;

/** Контроллер контрольных точек проекта: создание, обновление и подтверждение выполнения */
class MilestoneController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            (new Middleware('can:view,milestone'))->only(['show']),
            (new Middleware('can:update,milestone'))->only(['update']),
        ];
    }

    /** Возвращает список контрольных точек для указанного проекта */
    #[OA\Get(
        path: '/projects/{project}/milestones',
        summary: 'List milestones for a project',
        tags: ['Milestones'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of milestones'),
            new OA\Response(response: 403, description: 'Not authorized to view milestones for this project'),
        ]
    )]
    public function index(Project $project): JsonResponse
    {
        $this->authorize('viewAny', [Milestone::class, $project]);

        return $this->apiJson(MilestoneResource::collection($project->milestones()->with('approvedBy')->get()));
    }

    /** Создаёт новую контрольную точку для проекта со статусом «ожидает» */
    #[OA\Post(
        path: '/projects/{project}/milestones',
        summary: '[Mentor] Create a new pending milestone for a project',
        tags: ['Milestones'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Milestone created'),
            new OA\Response(response: 403, description: 'Not authorized to update this project'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreMilestoneRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $milestone = $project->milestones()->create(array_merge($request->validated(), [
            'status' => 'pending',
            'completion_percentage' => 0,
        ]));

        event(new \App\Events\MilestoneChanged($milestone, 'created'));

        return $this->apiJson(new MilestoneResource($milestone), 201);
    }

    /** Возвращает детали одной контрольной точки */
    #[OA\Get(
        path: '/milestones/{milestone}',
        summary: 'Get a single milestone',
        tags: ['Milestones'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'milestone', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Milestone detail'),
            new OA\Response(response: 403, description: 'Not authorized to view this milestone'),
            new OA\Response(response: 404, description: 'Milestone not found'),
        ]
    )]
    public function show(Milestone $milestone): JsonResponse
    {
        return $this->apiJson(new MilestoneResource($milestone->load('approvedBy')));
    }

    /** Обновляет данные контрольной точки */
    #[OA\Put(
        path: '/milestones/{milestone}',
        summary: 'Update a milestone',
        tags: ['Milestones'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'milestone', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Milestone updated'),
            new OA\Response(response: 403, description: 'Not authorized to update this milestone'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateMilestoneRequest $request, Milestone $milestone): JsonResponse
    {
        $milestone->update($request->validated());

        event(new \App\Events\MilestoneChanged($milestone, 'updated'));

        return $this->apiJson(new MilestoneResource($milestone->load('approvedBy')));
    }

    /** Подтверждает выполнение контрольной точки (ментор или администратор) */
    #[OA\Post(
        path: '/milestones/{milestone}/approve',
        summary: '[Mentor/Admin] Approve a milestone',
        tags: ['Milestones'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'milestone', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Milestone approved'),
            new OA\Response(response: 403, description: 'Not authorized to approve this milestone'),
        ]
    )]
    public function approve(Request $request, Milestone $milestone): JsonResponse
    {
        $this->authorize('approve', $milestone);

        $milestone->markAsApproved($request->user()->id);

        event(new \App\Events\MilestoneChanged($milestone, 'approved'));

        return $this->apiJson([
            'message' => 'Milestone approved successfully',
            'data'    => new MilestoneResource($milestone->load('approvedBy'))
        ]);
    }
}
