<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Mentorship;
use App\Http\Resources\ConsultationResource;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ConsultationController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mentorship_id' => 'required|exists:mentorships,id',
            'milestone_id'  => 'nullable|exists:milestones,id',
            'scheduled_at'  => 'required|date|after:now',
        ]);

        $mentorship = Mentorship::findOrFail($validated['mentorship_id']);

        if ($mentorship->mentor_id !== $request->user()->id) {
            return response()->json(['message' => 'Only the mentor can schedule consultations'], 403);
        }

        $consultation = Consultation::create([
            ...$validated,
            'mentor_id' => $request->user()->id,
        ]);

        return response()->json(new ConsultationResource($consultation), 201);
    }

    public function update(Request $request, Consultation $consultation)
    {
        $this->authorize('update', $consultation);

        $validated = $request->validate([
            'scheduled_at'    => 'sometimes|date',
            'completed_at'    => 'sometimes|nullable|date',
            'notes'           => 'sometimes|string|nullable',
            'recommendations' => 'sometimes|string|nullable',
        ]);

        $consultation->update($validated);

        return response()->json(new ConsultationResource($consultation->load(['mentor', 'milestone'])));
    }
}
