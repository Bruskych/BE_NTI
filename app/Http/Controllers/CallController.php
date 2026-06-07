<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Http\Resources\CallResource;
use App\Http\Requests\StoreCallRequest;
use App\Http\Requests\UpdateCallRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CallController extends Controller
{
    use AuthorizesRequests;

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Call::class);

        $calls = Call::with(['program', 'specializations'])->latest()->get();
        return response()->json(CallResource::collection($calls));
    }

    public function show(Call $call): JsonResponse
    {
        $this->authorize('view', $call);

        $call->load(['program', 'specializations', 'evaluationTemplate']);

        return response()->json(new CallResource($call));
    }

    public function store(StoreCallRequest $request): JsonResponse
    {
        $this->authorize('create', Call::class);

        $call = DB::transaction(function () use ($request) {
            $call = Call::create($request->validated());
            $call->specializations()->sync($request->specialization_ids);
            return $call;
        });

        return response()->json(new CallResource($call->load(['program', 'specializations'])), 201);
    }

    public function update(UpdateCallRequest $request, Call $call): JsonResponse
    {
        $this->authorize('update', $call);

        $call = DB::transaction(function () use ($request, $call) {
            $call->update($request->validated());

            if ($request->has('specialization_ids')) {
                $call->specializations()->sync($request->specialization_ids);
            }
            return $call;
        });

        return response()->json(new CallResource($call->load(['program', 'specializations'])));
    }
}
