<?php

namespace App\Http\Controllers;

use App\Models\{Team, User, Notification};
use App\Http\Resources\TeamResource;
use App\Http\Requests\{StoreTeamRequest, UpdateTeamRequest, RemoveMemberRequest};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\NotificationService;

class TeamController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Team::class, 'team');
    }

    public function index(): JsonResponse
    {
        return response()->api(TeamResource::collection(Team::with(['members', 'leader'])->get()));
    }

    public function show(Team $team): JsonResponse
    {
        return response()->api(new TeamResource($team->load(['members', 'leader'])));
    }

    public function store(StoreTeamRequest $request): JsonResponse
    {
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

        return response()->api(new TeamResource($team->load(['members', 'leader'])), 201);
    }

    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $team->update($request->validated());
        return response()->api(new TeamResource($team->load(['members', 'leader'])));
    }

    public function destroy(Team $team): JsonResponse
    {
        if ($team->members()->count() > 1) {
            return response()->api(['message' => 'Cannot delete team with members.'], 422);
        }

        $team->delete();
        return response()->api(['message' => 'Team deleted successfully.']);
    }

    public function invite(Request $request, Team $team, NotificationService $service): JsonResponse
    {
        $this->authorize('invite', $team);

        $validated = $request->validate(['email' => 'required|email']);
        $invitee = User::where('email', $validated['email'])->first();

        if (!$invitee) return response()->api(['message' => 'User not found.'], 404);
        if ($invitee->id === $request->user()->id) return response()->api(['message' => 'Cannot invite yourself.'], 422);
        if ($team->hasMember($invitee->id)) return response()->api(['message' => 'User is already a member.'], 422);
        if ($invitee->teams()->exists()) return response()->api(['message' => 'User is in another team.'], 422);

        $service->send($invitee, [
            'type'      => 'team_invite',
            'title'     => 'Team Invitation',
            'message'   => "You have been invited to join team {$team->name}",
            'data_json' => [
                'team_id'   => $team->id,
                'team_name' => $team->name,
                'sender'    => $request->user()->name
            ]
        ]);

        return response()->api(['message' => 'Invitation successfully sent.']);
    }

    public function myTeam(Request $request): JsonResponse
    {
        $team = $request->user()->teams()->first();
        if (!$team) return response()->api(['message' => 'You are not in a team.'], 404);

        return response()->api(new TeamResource($team->load(['members', 'leader'])));
    }

    public function leave(Request $request): JsonResponse
    {
        $user = $request->user();
        $team = $user->teams()->first();

        if (!$team) return response()->api(['message' => 'You are not in a team.'], 404);
        if ($team->leader_id === $user->id) {
            return response()->api(['message' => 'Leaders cannot leave. Delete team or transfer leadership.'], 422);
        }

        $team->members()->detach($user->id);
        return response()->api(['message' => 'You have left the team.']);
    }

    public function removeMember(RemoveMemberRequest $request, Team $team): JsonResponse
    {
        $this->authorize('removeMember', $team);

        $userId = $request->validated('user_id');

        if (!$team->hasMember($userId)) {
            return response()->api(['message' => 'User is not in this team.'], 404);
        }
        if ($team->leader_id == $userId) {
            return response()->api(['message' => 'You cannot remove the team leader.'], 422);
        }

        $team->members()->detach($userId);
        return response()->api(['message' => 'Member removed successfully.']);
    }
}
