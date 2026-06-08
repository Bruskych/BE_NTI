<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Http\Resources\ChallengeResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreChallengeRequest;
use App\Http\Requests\UpdateChallengeRequest;

class ChallengeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Challenge::class, 'challenge');
    }

    public function index(Request $request): JsonResponse
    {
        $challenges = Challenge::with(['program', 'organization', 'specializations'])
            ->withCount('applications')
            ->visibleTo($request->user())
            ->latest()
            ->get();

        return response()->api(ChallengeResource::collection($challenges));
    }

    public function store(StoreChallengeRequest $request): JsonResponse
    {
        $challenge = Challenge::create($request->validated());

        if ($request->has('specialization_ids')) {
            $challenge->specializations()->sync($request->specialization_ids);
        }

        return response()->api(new ChallengeResource($challenge), 201);
    }

    public function show(Challenge $challenge): JsonResponse
    {
        $challenge->load(['program', 'organization', 'specializations', 'productOwner']);

        return response()->api(new ChallengeResource($challenge));
    }

    public function update(UpdateChallengeRequest $request, Challenge $challenge): JsonResponse
    {
        $challenge->update($request->validated());

        if ($request->has('specialization_ids')) {
            $challenge->specializations()->sync($request->specialization_ids);
        }

        return response()->api(new ChallengeResource($challenge->load(['program', 'organization', 'specializations'])));
    }

    public function destroy(Challenge $challenge): JsonResponse
    {
        $challenge->delete();

        return response()->api(['message' => 'Challenge deleted successfully']);
    }
}
