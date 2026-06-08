<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Http\Resources\OrganizationResource;
use App\Http\Requests\{StoreOrganizationRequest, UpdateOrganizationRequest};
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrganizationController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Organization::class, 'organization');
    }

    public function show(Organization $organization): JsonResponse
    {
        return response()->api(new OrganizationResource($organization->load(['users'])));
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $organization = DB::transaction(function () use ($request) {
            $org = Organization::create($request->validated());
            $org->users()->attach($request->user(), ['role' => 'owner']);
            return $org;
        });

        if ($request->hasFile('logo')) {
            $organization->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        return response()->api(new OrganizationResource($organization->load(['users'])), 201);
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        $organization->update($request->validated());

        if ($request->hasFile('logo')) {
            $organization->clearMediaCollection('logo');
            $organization->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        return response()->api(new OrganizationResource($organization->load(['users'])));
    }

    public function destroy(Organization $organization): JsonResponse
    {
        $organization->delete();
        return response()->api(null, 204);
    }
}
