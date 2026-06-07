<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function show(Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->load(['milestones', 'application.team.members']));
    }

    public function store(StoreProjectRequest $request): ProjectResource
    {
        $this->authorize('create', Project::class);

        $project = Project::create($request->validated());

        return new ProjectResource($project);
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return new ProjectResource($project);
    }
}
