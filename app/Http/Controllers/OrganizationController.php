<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Http\Resources\OrganizationResource;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Requests\StoreOrganizationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    public function show(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        return response()->json(new OrganizationResource($organization->load(['users'])));
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('update', $organization);

        $organization->update($request->validated());

        if ($request->hasFile('logo')) {
            $organization->clearMediaCollection('logo');
            $organization->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        return response()->json(
            new OrganizationResource($organization->load(['users']))
        );
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $this->authorize('create', Organization::class);

        $organization = DB::transaction(function () use ($request) {
            $org = Organization::create($request->validated());
            $org->users()->attach($request->user(), ['role' => 'owner']);
            return $org;
        });

        if ($request->hasFile('logo')) {
            $organization->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        return response()->json(new OrganizationResource($organization->load(['users'])), 201);
    }

    public function destroy(Organization $organization): JsonResponse
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        return response()->json(null, 204);
    }
}
