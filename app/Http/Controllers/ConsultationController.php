<?php

namespace App\Http\Controllers;

use App\Models\{Consultation, Mentorship};
use App\Http\Resources\ConsultationResource;
use App\Http\Requests\{StoreConsultationRequest, UpdateConsultationRequest};
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ConsultationController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Consultation::class, 'consultation');
    }

    public function store(StoreConsultationRequest $request): JsonResponse
    {
        $this->authorize('create', Consultation::class);

        $mentorship = Mentorship::findOrFail($request->mentorship_id);

        if ($mentorship->mentor_id !== $request->user()->id) {
            return response()->api(['message' => 'You are not the mentor of this mentorship.'], 403);
        }

        $consultation = Consultation::create(array_merge($request->validated(), [
            'mentor_id' => $request->user()->id,
        ]));

        return response()->api(new ConsultationResource($consultation), 201);
    }

    public function update(UpdateConsultationRequest $request, Consultation $consultation): JsonResponse
    {
        $consultation->update($request->validated());

        return response()->api(new ConsultationResource($consultation->load(['mentor', 'milestone'])));
    }

    public function destroy(Consultation $consultation): JsonResponse
    {
        $consultation->delete();

        return response()->api(['message' => 'Consultation deleted successfully.']);
    }
}
