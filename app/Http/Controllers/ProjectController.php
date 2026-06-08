<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\{StoreProjectRequest, UpdateProjectRequest};
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Project::class, 'project');
    }

    public function show(Project $project): JsonResponse
    {
        return response()->api(new ProjectResource($project->load(['milestones', 'application.team.members'])));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $project = Project::create($request->validated());

        return response()->api(new ProjectResource($project), 201);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->validated());

        return response()->api(new ProjectResource($project));
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->api(null, 204);
    }
}
