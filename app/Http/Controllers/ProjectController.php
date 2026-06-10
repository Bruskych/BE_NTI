<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\{StoreProjectRequest, UpdateProjectRequest};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use OpenApi\Attributes as OA;

/** Контроллер проектов: CRUD и фильтрация по правам доступа */
class ProjectController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return static::resourcePolicyMiddleware(Project::class, 'project');
    }

    /** Возвращает список проектов с учётом прав доступа пользователя */
    #[OA\Get(
        path: '/projects',
        summary: 'List projects (all for admins/holders of projects.view-all, own team/mentored projects otherwise)',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of projects'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Project::with(['application.team', 'mentorship', 'milestones']);

        if (!$user->can('projects.view-all')) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('application.team.members', fn ($q2) => $q2->where('users.id', $user->id))
                    ->orWhereHas('mentorship', fn ($q2) => $q2->where('mentor_id', $user->id));
            });
        }

        return $this->apiJson(ProjectResource::collection($query->latest()->get()));
    }

    /** Возвращает детали одного проекта с контрольными точками и участниками команды */
    #[OA\Get(
        path: '/projects/{project}',
        summary: 'Get a single project with milestones and team members',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Project detail'),
            new OA\Response(response: 403, description: 'Not authorized to view this project'),
            new OA\Response(response: 404, description: 'Project not found'),
        ]
    )]
    public function show(Project $project): JsonResponse
    {
        return $this->apiJson(new ProjectResource($project->load(['milestones', 'application.team.members'])));
    }

    /** Создаёт проект на основе одобренной заявки */
    #[OA\Post(
        path: '/projects',
        summary: 'Create a project from an approved application',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Project created'),
            new OA\Response(response: 403, description: 'Not authorized to create projects'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $project = Project::create($request->validated());

        return $this->apiJson(new ProjectResource($project), 201);
    }

    /** Обновляет данные проекта */
    #[OA\Put(
        path: '/projects/{project}',
        summary: 'Update a project',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Project updated'),
            new OA\Response(response: 403, description: 'Not authorized to update this project'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->validated());

        return $this->apiJson(new ProjectResource($project));
    }

    /** Удаляет проект */
    #[OA\Delete(
        path: '/projects/{project}',
        summary: 'Delete a project',
        tags: ['Projects'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Project deleted'),
            new OA\Response(response: 403, description: 'Not authorized to delete this project'),
        ]
    )]
    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return $this->apiJson(null, 204);
    }
}
