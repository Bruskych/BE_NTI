<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Project;
use App\Http\Resources\MilestoneResource;
use App\Http\Requests\StoreMilestoneRequest;
use App\Http\Requests\UpdateMilestoneRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MilestoneController extends Controller
{
    use AuthorizesRequests;


    public function index(Project $project): JsonResponse
    {
        $this->authorize('viewAny', Milestone::class);

        $milestones = $project->milestones()->with('approvedBy')->get();

        return response()->json(MilestoneResource::collection($milestones));
    }

    public function show(Milestone $milestone): JsonResponse
    {
        $this->authorize('view', $milestone);

        return response()->json(new MilestoneResource($milestone->load('approvedBy')));
    }

    public function store(StoreMilestoneRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $milestone = $project->milestones()->create(array_merge($request->validated(), [
            'status' => 'pending',
            'completion_percentage' => 0,
        ]));

        return response()->json(new MilestoneResource($milestone), 201);
    }

    public function update(UpdateMilestoneRequest $request, Milestone $milestone): JsonResponse
    {
        $this->authorize('update', $milestone);

        $milestone->update($request->validated());

        return response()->json(new MilestoneResource($milestone->load('approvedBy')));
    }

    public function approve(Milestone $milestone): JsonResponse
    {
        $this->authorize('approve', $milestone);

        $milestone->markAsApproved(auth()->id());

        return response()->json([
            'message' => 'Milestone approved successfully',
            'data'    => new MilestoneResource($milestone->load('approvedBy'))
        ]);
    }
}
