<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Project;
use App\Http\Resources\MilestoneResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MilestoneController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline'    => 'required|date|after:today',
        ]);

        $milestone = $project->milestones()->create([
            ...$validated,
            'status' => 'pending',
            'completion_percentage' => 0,
        ]);

        return response()->json(new MilestoneResource($milestone), 201);
    }

    public function update(Request $request, Milestone $milestone)
    {
        $this->authorize('update', $milestone);

        $validated = $request->validate([
            'title'                 => 'sometimes|string|max:255',
            'description'           => 'nullable|string',
            'completion_percentage' => 'sometimes|integer|min:0|max:100',
            'status'                => 'sometimes|string|in:pending,in_progress,completed',
        ]);

        $milestone->update($validated);

        return response()->json(new MilestoneResource($milestone->load('approvedBy')));
    }

    public function approve(Milestone $milestone)
    {
        $this->authorize('approve', $milestone);

        $milestone->update([
            'approved_by' => auth()->id(),
            'status'      => 'completed',
            'completed_at' => now(),
            'completion_percentage' => 100,
        ]);

        return response()->json([
            'message' => 'Milestone approved successfully',
            'data'    => new MilestoneResource($milestone->load('approvedBy'))
        ]);
    }
}
