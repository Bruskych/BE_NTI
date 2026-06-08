<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Http\Resources\ApplicationResource;
use App\Http\Requests\{StoreApplicationRequest, UpdateApplicationRequest};
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Application::class, 'application');
    }

    public function index(): JsonResponse
    {
        return response()->api(ApplicationResource::collection(Application::paginate()));
    }

    public function show(Application $application): JsonResponse
    {
        return response()->api(new ApplicationResource($application->load(['team', 'organization'])));
    }

    public function store(StoreApplicationRequest $request, ApplicationService $service): JsonResponse
    {
        $this->authorize('create', Application::class);
        $team = $request->user()->teams()->first();

        $application = $service->createApplication($request->validated(), $team->id, $request->user()->id);

        return response()->api(new ApplicationResource($application), 201);
    }

    public function update(UpdateApplicationRequest $request, Application $application): JsonResponse
    {
        $application->update($request->validated());

        return response()->api(new ApplicationResource($application));
    }

    public function submit(Request $request, Application $application, ApplicationService $service): JsonResponse
    {
        $this->authorize('submit', $application);
        $service->submitApplication($application, $request->user()->id);
        return response()->api(['message' => 'Application submitted successfully']);
    }

    public function destroy(Application $application): JsonResponse
    {
        $application->delete();
        return response()->api(null, 204);
    }
}
