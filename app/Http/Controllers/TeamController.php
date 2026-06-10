<?php

namespace App\Http\Controllers;

use App\Models\{Team, User, Notification};
use App\Http\Resources\TeamResource;
use App\Http\Requests\{StoreTeamRequest, UpdateTeamRequest, RemoveMemberRequest};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Services\NotificationService;
use OpenApi\Attributes as OA;

/** Контроллер команд: CRUD, приглашения, выход и удаление участников */
class TeamController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return static::resourcePolicyMiddleware(Team::class, 'team');
    }

    /** Возвращает список всех команд с участниками и лидером */
    #[OA\Get(
        path: '/teams',
        summary: 'List all teams with members and leader',
        tags: ['Teams'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of teams'),
        ]
    )]
    public function index(): JsonResponse
    {
        return $this->apiJson(TeamResource::collection(Team::with(['members', 'leader'])->get()));
    }

    /** Возвращает детали одной команды */
    #[OA\Get(
        path: '/teams/{team}',
        summary: 'Get a single team with members and leader',
        tags: ['Teams'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'team', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Team detail'),
            new OA\Response(response: 404, description: 'Team not found'),
        ]
    )]
    public function show(Team $team): JsonResponse
    {
        return $this->apiJson(new TeamResource($team->load(['members', 'leader'])));
    }

    /** Создаёт команду и назначает текущего пользователя лидером */
    #[OA\Post(
        path: '/teams',
        summary: 'Create a team and become its leader',
        tags: ['Teams'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Team created'),
            new OA\Response(response: 403, description: 'Not authorized to create teams'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
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

        return $this->apiJson(new TeamResource($team->load(['members', 'leader'])), 201);
    }

    /** Обновляет данные команды */
    #[OA\Put(
        path: '/teams/{team}',
        summary: 'Update a team',
        tags: ['Teams'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'team', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Team updated'),
            new OA\Response(response: 403, description: 'Not authorized to update this team'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $team->update($request->validated());
        return $this->apiJson(new TeamResource($team->load(['members', 'leader'])));
    }

    /** Удаляет команду, если в ней нет других участников */
    #[OA\Delete(
        path: '/teams/{team}',
        summary: 'Delete a team (only if it has no other members)',
        tags: ['Teams'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'team', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Team deleted'),
            new OA\Response(response: 403, description: 'Not authorized to delete this team'),
            new OA\Response(response: 422, description: 'Cannot delete a team with members'),
        ]
    )]
    public function destroy(Team $team): JsonResponse
    {
        if ($team->members()->count() > 1) {
            return $this->apiJson(['message' => 'Cannot delete team with members.'], 422);
        }

        $members = $team->members()->get();
        $teamName = $team->name;

        $team->delete();

        event(new \App\Events\TeamDeleted($members, $teamName));

        return $this->apiJson(['message' => 'Team deleted successfully.']);
    }

    /** Отправляет приглашение в команду пользователю по email */
    #[OA\Post(
        path: '/teams/{team}/invite',
        summary: '[Leader] Invite a user to the team by email',
        tags: ['Teams'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'team', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Invitation sent'),
            new OA\Response(response: 403, description: 'Not authorized to invite to this team'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 422, description: 'User cannot be invited (already in a team, etc.)'),
        ]
    )]
    public function invite(Request $request, Team $team, NotificationService $service): JsonResponse
    {
        $this->authorize('invite', $team);

        $validated = $request->validate(['email' => 'required|email']);
        $invitee = User::where('email', $validated['email'])->first();

        if (!$invitee) return $this->apiJson(['message' => 'User not found.'], 404);
        if ($invitee->id === $request->user()->id) return $this->apiJson(['message' => 'Cannot invite yourself.'], 422);
        if ($team->hasMember($invitee->id)) return $this->apiJson(['message' => 'User is already a member.'], 422);
        if ($invitee->teams()->exists()) return $this->apiJson(['message' => 'User is in another team.'], 422);

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

        return $this->apiJson(['message' => 'Invitation successfully sent.']);
    }

    /** Возвращает команду текущего пользователя */
    #[OA\Get(
        path: '/teams/my-team',
        summary: 'Get the current user\'s own team',
        tags: ['Teams'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Own team detail'),
            new OA\Response(response: 404, description: 'You are not in a team'),
        ]
    )]
    public function myTeam(Request $request): JsonResponse
    {
        $team = $request->user()->teams()->first();
        if (!$team) return $this->apiJson(['message' => 'You are not in a team.'], 404);

        return $this->apiJson(new TeamResource($team->load(['members', 'leader'])));
    }

    /** Выход текущего пользователя из команды */
    #[OA\Post(
        path: '/teams/leave',
        summary: 'Leave the current user\'s own team (leaders cannot leave)',
        tags: ['Teams'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Left the team'),
            new OA\Response(response: 404, description: 'You are not in a team'),
            new OA\Response(response: 422, description: 'Leaders cannot leave their team'),
        ]
    )]
    public function leave(Request $request): JsonResponse
    {
        $user = $request->user();
        $team = $user->teams()->first();

        if (!$team) return $this->apiJson(['message' => 'You are not in a team.'], 404);
        if ($team->leader_id === $user->id) {
            return $this->apiJson(['message' => 'Leaders cannot leave. Delete team or transfer leadership.'], 422);
        }

        $team->members()->detach($user->id);

        event(new \App\Events\MemberLeftTeam($user, $team));

        return $this->apiJson(['message' => 'You have left the team.']);
    }

    /** Удаляет участника из команды (только лидер) */
    #[OA\Post(
        path: '/teams/{team}/remove-member',
        summary: '[Leader] Remove a member from the team',
        tags: ['Teams'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'team', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Member removed'),
            new OA\Response(response: 403, description: 'Not authorized to remove members from this team'),
            new OA\Response(response: 404, description: 'User is not a member of this team'),
            new OA\Response(response: 422, description: 'Cannot remove the team leader'),
        ]
    )]
    public function removeMember(RemoveMemberRequest $request, Team $team): JsonResponse
    {
        $this->authorize('removeMember', $team);

        $userId = $request->validated('user_id');
        $userToRemove = User::findOrFail($userId);

        if (!$team->hasMember($userId)) {
            return $this->apiJson(['message' => 'User is not in this team.'], 404);
        }
        if ($team->leader_id == $userId) {
            return $this->apiJson(['message' => 'You cannot remove the team leader.'], 422);
        }

        $team->members()->detach($userId);

        event(new \App\Events\MemberKickedFromTeam($userToRemove, $team));

        return $this->apiJson(['message' => 'Member removed successfully.']);
    }
}
