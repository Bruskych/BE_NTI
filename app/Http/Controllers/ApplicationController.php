<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Http\Resources\ApplicationResource;
use App\Http\Requests\StoreApplicationRequest;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreApplicationRequest $request, ApplicationService $service): JsonResponse
    {
        $team = $request->user()->teams()->first();

        if (!$team || $team->leader_id !== $request->user()->id) {
            return response()->json(['message' => 'Only team leader can create application.'], 403);
        }

        $application = $service->createApplication($request->validated(), $team->id, $request->user()->id);

        return response()->json(new ApplicationResource($application), 201);
    }

    public function submit(Request $request, Application $application, ApplicationService $service): JsonResponse
    {
        $this->authorize('submit', $application);

        $service->submitApplication($application, $request->user()->id);

        return response()->json(['message' => 'Application submitted successfully']);
    }
}
