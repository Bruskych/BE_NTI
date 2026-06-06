<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    /**
     * Просмотр проекта.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->load(['milestones', 'application.team.members']));
    }

    /**
     * Обновление проекта.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'sometimes|string',
        ]);

        $project->update($validated);

        return new ProjectResource($project->load(['milestones', 'application.team']));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'status'         => 'nullable|string',
        ]);

        $project = Project::create($validated);

        return new ProjectResource($project->load(['application.team']));
    }
}
