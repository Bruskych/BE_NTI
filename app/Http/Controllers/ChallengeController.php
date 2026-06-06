<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Http\Resources\ChallengeResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ChallengeController extends Controller
{
    use AuthorizesRequests;

    /**
     * Получить список заданий с умной фильтрацией по ролям.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Challenge::with(['program', 'organization', 'specializations']);

        if (!$user || $user->hasRole('student') || $user->hasRole('visitor')) {
            $query->whereIn('status', ['published', 'pairing', 'assigned', 'active']);

        } elseif ($user->hasRole('company')) {
            $myCompanyIds = $user->organizations()->pluck('organizations.id')->toArray();

            $query->where(function ($q) use ($myCompanyIds) {
                $q->whereIn('status', ['published', 'pairing', 'assigned', 'active'])
                    ->orWhere(function ($subQuery) use ($myCompanyIds) {
                        $subQuery->where('status', 'draft')
                            ->whereIn('organization_id', $myCompanyIds);
                    });
            });
        }

        $challenges = $query->latest()->get();

        return response()->json(ChallengeResource::collection($challenges));
    }

    /**
     * Посмотреть детальную информацию.
     */
    public function show(int $id): JsonResponse
    {
        $challenge = Challenge::with(['program', 'organization', 'productOwner', 'specializations'])->findOrFail($id);

        $this->authorize('view', $challenge);

        return response()->json(new ChallengeResource($challenge));
    }

    /**
     * Создать новое задание (С защитой от подмены ID и поддержкой Админки).
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Challenge::class);

        $user = $request->user();

        $rules = [
            'program_id'              => 'required|exists:programs,id',
            'title'                   => 'required|string|max:255',
            'description'             => 'required|string',
            'technical_specification' => 'nullable|string',
            'budget'                  => 'nullable|numeric|min:0',
            'product_owner_id'        => 'nullable|exists:users,id',
            'deadline'                => 'required|date|after:now',
            'status'                  => 'required|in:draft,published,pairing,assigned,active,closed',
            'max_applications'        => 'required|integer|min:1',
            'backlog_order'           => 'nullable|integer',
            'specialization_ids'      => 'required|array',
            'specialization_ids.*'    => 'exists:specializations,id',
        ];

        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            $rules['organization_id'] = 'required|exists:organizations,id';
        }

        $validated = $request->validate($rules);

        if (!$user->hasAnyRole(['admin', 'super_admin'])) {
            $organization = $user->organizations()->first();
            $validated['organization_id'] = $organization->id;

            if (empty($validated['product_owner_id'])) {
                $validated['product_owner_id'] = $user->id;
            }
        }

        $challenge = Challenge::create($validated);
        $challenge->specializations()->sync($validated['specialization_ids']);

        return response()->json(
            new ChallengeResource($challenge->load(['program', 'organization', 'specializations'])),
            201
        );
    }

    /**
     * Обновить существующее задание.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $challenge = Challenge::findOrFail($id);

        $this->authorize('update', $challenge);

        $validated = $request->validate([
            'program_id'              => 'sometimes|required|exists:programs,id',
            'title'                   => 'sometimes|required|string|max:255',
            'description'             => 'sometimes|required|string',
            'technical_specification' => 'nullable|string',
            'budget'                  => 'nullable|numeric|min:0',
            'product_owner_id'        => 'nullable|exists:users,id',
            'deadline'                => 'sometimes|required|date',
            'status'                  => 'sometimes|required|in:draft,published,pairing,assigned,active,closed',
            'max_applications'        => 'sometimes|required|integer|min:1',
            'backlog_order'           => 'nullable|integer',
            'specialization_ids'      => 'sometimes|required|array',
            'specialization_ids.*'    => 'exists:specializations,id',
        ]);

        $challenge->update($validated);

        if (isset($validated['specialization_ids'])) {
            $challenge->specializations()->sync($validated['specialization_ids']);
        }

        return response()->json(new ChallengeResource($challenge->load(['program', 'organization', 'specializations'])));
    }
}
