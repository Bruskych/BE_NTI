<?php

namespace App\Http\Controllers;

use App\Models\{Milestone, Project};
use App\Http\Resources\MilestoneResource;
use App\Http\Requests\{StoreMilestoneRequest, UpdateMilestoneRequest};
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MilestoneController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Milestone::class, 'milestone');
    }

    public function index(Project $project): JsonResponse
    {
        $this->authorize('viewAny', [Milestone::class, $project]);

        return response()->api(MilestoneResource::collection($project->milestones()->with('approvedBy')->get()));
    }

    public function store(StoreMilestoneRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $milestone = $project->milestones()->create(array_merge($request->validated(), [
            'status' => 'pending',
            'completion_percentage' => 0,
        ]));

        return response()->api(new MilestoneResource($milestone), 201);
    }

    public function show(Milestone $milestone): JsonResponse
    {
        return response()->api(new MilestoneResource($milestone->load('approvedBy')));
    }

    public function update(UpdateMilestoneRequest $request, Milestone $milestone): JsonResponse
    {
        $milestone->update($request->validated());

        return response()->api(new MilestoneResource($milestone->load('approvedBy')));
    }

    public function approve(Milestone $milestone): JsonResponse
    {
        $this->authorize('approve', $milestone);

        $milestone->markAsApproved(auth()->id());

        return response()->api([
            'message' => 'Milestone approved successfully',
            'data'    => new MilestoneResource($milestone->load('approvedBy'))
        ]);
    }
}
