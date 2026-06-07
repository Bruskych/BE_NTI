<?php

namespace App\Http\Controllers;

use App\Models\{Team, User, Notification};
use App\Http\Resources\TeamResource;
use App\Http\Requests\{StoreTeamRequest, UpdateTeamRequest};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeamController extends Controller
{
    use AuthorizesRequests;

    public function show(Team $team): JsonResponse
    {
        return response()->json(new TeamResource($team->load(['members', 'leader'])));
    }
    public function store(StoreTeamRequest $request): JsonResponse
    {
        if ($request->user()->teams()->exists()) {
            return response()->json(['message' => 'You are already a member of a team.'], 422);
        }

        $team = DB::transaction(function () use ($request) {
            $team = Team::create(array_merge($request->validated(), [
                'leader_id' => $request->user()->id,
                'status'    => 'active',
            ]));

            $team->members()->attach($request->user()->id, [
                'role'      => 'leader',
                'joined_at' => now(),
            ]);

            return $team;
        });

        return response()->json(new TeamResource($team->load(['members', 'leader'])), 201);
    }

    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $team->update($request->validated());

        return response()->json(new TeamResource($team->load(['members', 'leader'])));
    }

    public function invite(Request $request, Team $team): JsonResponse
    {
        $this->authorize('invite', $team);

        $validated = $request->validate(['email' => 'required|email']);
        $invitee = User::where('email', $validated['email'])->first();

        if (!$invitee) return response()->json(['message' => 'User not found.'], 404);
        if ($invitee->id === $request->user()->id) return response()->json(['message' => 'Cannot invite yourself.'], 422);
        if ($team->hasMember($invitee->id)) return response()->json(['message' => 'User is already a member.'], 422);
        if ($invitee->teams()->exists()) return response()->json(['message' => 'User is in another team.'], 422);

        if (Notification::forTeamInvite()
            ->forUser($invitee->id)
            ->forTeam($team->id)
            ->unread()
            ->exists())
        {
            return response()->json(['message' => 'Invitation already sent.'], 422);
        }

        Notification::create([
            'user_id'   => $invitee->id,
            'type'      => 'team_invite',
            'channel'   => 'system',
            'title'     => 'Team Invitation',
            'message'   => "You have been invited to join team {$team->name}",
            'data_json' => ['team_id' => $team->id, 'team_name' => $team->name, 'sender' => $request->user()->name]
        ]);

        return response()->json(['message' => 'Invitation successfully sent.']);
    }

    public function myTeam(Request $request): JsonResponse
    {
        $team = $request->user()->teams()->first();

        if (!$team) {
            return response()->json(['message' => 'You are not in team.'], 404);
        }

        return response()->json(new TeamResource($team->load(['members', 'leader'])));
    }

    public function leave(Request $request): JsonResponse
    {
        $user = $request->user();
        $team = $user->teams()->first();

        if (!$team) {
            return response()->json(['message' => 'You are not in a team.'], 404);
        }

        if ($team->leader_id === $user->id) {
            return response()->json([
                'message' => 'Team leaders cannot leave. Please delete the team or transfer leadership first.'
            ], 422);
        }

        $team->members()->detach($user->id);

        return response()->json(['message' => 'You have left the team successfully.']);
    }

    public function myNotifications(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Notification::forTeamInvite()
                ->forUser($request->user()->id)
                ->unread()
                ->latest()
                ->get()
        ]);
    }
}
