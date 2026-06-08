<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMentorshipRequest;
use App\Http\Requests\UpdateMentorshipRequest;
use App\Http\Resources\MentorshipResource;
use App\Models\Mentorship;
use Illuminate\Http\JsonResponse;

class MentorshipController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Mentorship::class);
        return MentorshipResource::collection(Mentorship::with(['project', 'mentor'])->get());
    }

    public function store(StoreMentorshipRequest $request): MentorshipResource
    {
        $this->authorize('create', Mentorship::class);
        $mentorship = Mentorship::create($request->validated());
        return new MentorshipResource($mentorship);
    }

    public function show(Mentorship $mentorship): MentorshipResource
    {
        $this->authorize('view', $mentorship);
        return new MentorshipResource($mentorship->load(['project', 'mentor', 'consultations']));
    }

    public function update(UpdateMentorshipRequest $request, Mentorship $mentorship): MentorshipResource
    {
        $this->authorize('update', $mentorship);
        $mentorship->update($request->validated());
        return new MentorshipResource($mentorship);
    }

    public function destroy(Mentorship $mentorship): JsonResponse
    {
        $this->authorize('delete', $mentorship);
        $mentorship->delete();
        return response()->json(['message' => 'Mentorship deleted successfully'], 200);
    }
}
