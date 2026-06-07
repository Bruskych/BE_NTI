<?php

namespace App\Http\Controllers;

use App\Models\{Notification, Team};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TeamResource;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Notification::forTeamInvite()
                ->forUser($request->user()->id)
                ->unread()
                ->latest()
                ->get()
        ]);
    }

    public function accept(Notification $notification, Request $request): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($notification->isRead()) {
            return response()->json(['message' => 'Invitation already processed.'], 422);
        }

        $team = Team::find($notification->team_id);

        if (!$team) return response()->json(['message' => 'Team does not exist.'], 404);
        if ($request->user()->teams()->exists()) return response()->json(['message' => 'Already in a team.'], 422);

        if (method_exists($team, 'isFull') && $team->isFull()) {
            return response()->json(['message' => 'Team is full.'], 422);
        }

        DB::transaction(function () use ($notification, $team, $request) {
            $team->members()->attach($request->user()->id, ['role' => 'member', 'joined_at' => now()]);
            $notification->markAsRead();
        });

        return response()->json(['message' => 'Joined successfully.', 'data' => new TeamResource($team)]);
    }

    public function reject(Notification $notification, Request $request): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($notification->isRead()) {
            return response()->json(['message' => 'Invitation already processed.'], 422);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Invitation declined successfully.']);
    }
}
