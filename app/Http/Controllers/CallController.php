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

    public function __construct()
    {
        $this->authorizeResource(Call::class, 'call');
    }

    public function index(): JsonResponse
    {
        $calls = Call::with(['program', 'specializations'])->latest()->get();
        return response()->api(CallResource::collection($calls));
    }

    public function show(Call $call): JsonResponse
    {
        $call->load(['program', 'specializations', 'evaluationTemplate']);
        return response()->api(new CallResource($call));
    }

    public function store(StoreCallRequest $request): JsonResponse
    {
        $call = DB::transaction(function () use ($request) {
            $call = Call::create($request->validated());
            if ($request->has('specialization_ids')) {
                $call->specializations()->sync($request->specialization_ids);
            }
            return $call;
        });

        return response()->api(new CallResource($call->load(['program', 'specializations'])), 201);
    }

    public function update(UpdateCallRequest $request, Call $call): JsonResponse
    {
        $call = DB::transaction(function () use ($request, $call) {
            $call->update($request->validated());
            if ($request->has('specialization_ids')) {
                $call->specializations()->sync($request->specialization_ids);
            }
            return $call;
        });

        return response()->api(new CallResource($call->load(['program', 'specializations'])));
    }

    public function destroy(Call $call): JsonResponse
    {
        $call->delete();
        return response()->api(['message' => 'Call deleted successfully.']);
    }

    public function open(Call $call): JsonResponse
    {
        $this->authorize('open', $call);
        $call->update(['status' => 'open']);

        return response()->api(new CallResource($call));
    }

    public function close(Call $call): JsonResponse
    {
        $this->authorize('close', $call);
        $call->update(['status' => 'closed']);

        return response()->api(new CallResource($call));
    }
}
