<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Http\Resources\ChallengeResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\StoreChallengeRequest;
use App\Http\Requests\UpdateChallengeRequest;

class ChallengeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $challenges = Challenge::with(['program', 'organization', 'specializations'])
            ->visibleTo($request->user())
            ->latest()
            ->get();

        return response()->json(ChallengeResource::collection($challenges));
    }

    public function store(StoreChallengeRequest $request): JsonResponse
    {
        $this->authorize('create', Challenge::class);

        $challenge = Challenge::create($request->validated());

        if ($request->has('specialization_ids')) {
            $challenge->specializations()->sync($request->specialization_ids);
        }

        return response()->json(new ChallengeResource($challenge), 201);
    }

    public function show(int $id): JsonResponse
    {
        $challenge = Challenge::with(['program', 'organization', 'specializations', 'productOwner'])
            ->findOrFail($id);

        $this->authorize('view', $challenge);

        return response()->json(new ChallengeResource($challenge));
    }

    public function update(UpdateChallengeRequest $request, int $id): JsonResponse
    {
        $challenge = Challenge::findOrFail($id);

        $this->authorize('update', $challenge);

        $challenge->update($request->validated());

        if ($request->has('specialization_ids')) {
            $challenge->specializations()->sync($request->specialization_ids);
        }

        return response()->json(new ChallengeResource($challenge->load(['program', 'organization', 'specializations'])));
    }

    public function destroy(int $id): JsonResponse
    {
        $challenge = Challenge::findOrFail($id);

        $this->authorize('delete', $challenge);

        $challenge->delete();

        return response()->json(['message' => 'Challenge deleted successfully']);
    }
}
