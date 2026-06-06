<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Http\Resources\OrganizationResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrganizationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Посмотреть профиль компании (Route Model Binding автоматически сделает findOrFail).
     */
    public function show(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        return response()->json(new OrganizationResource($organization->load(['users'])));
    }

    /**
     * Обновить профиль компании и её логотип.
     */
    public function update(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'tax_id'       => [
                'sometimes',
                'required',
                'string',
                'regex:/^\d{8,10}$/',
                Rule::unique('organizations', 'tax_id')->ignore($organization->id)
            ],
            'sector'       => 'sometimes|required|string|max:255',
            'website_link' => 'sometimes|required|url|max:500',
            'description'  => 'sometimes|required|string|max:2000',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $organization->update($validated);

        if ($request->hasFile('logo')) {
            $organization->clearMediaCollection('logo');
            $organization->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        return response()->json(
            new OrganizationResource($organization->load(['users']))
        );
    }
}
