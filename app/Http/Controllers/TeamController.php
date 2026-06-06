<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Http\Resources\TeamResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeamController extends Controller
{
    use AuthorizesRequests;

    /**
     * Создание команды (Eloquent + Transaction).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'capacity'    => 'required|integer|min:1',
        ]);

        $team = DB::transaction(function () use ($validated, $request) {
            $team = Team::create([
                'name'        => $validated['name'],
                'description' => $validated['description'],
                'capacity'    => $validated['capacity'],
                'leader_id'   => $request->user()->id,
                'status'      => 'active',
            ]);

            $team->members()->attach($request->user()->id, [
                'role'      => 'leader',
                'joined_at' => now(),
            ]);

            return $team;
        });

        return response()->json(
            new TeamResource($team->load(['members', 'leader'])),
            201
        );
    }

    /**
     * Обновление команды.
     */
    public function update(Request $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'capacity'    => 'sometimes|required|integer|min:1',
        ]);

        $team->update($validated);

        return response()->json(
            new TeamResource($team->load(['members', 'leader']))
        );
    }
}
