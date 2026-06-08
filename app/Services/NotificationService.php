<?php

namespace App\Services;

use App\Models\{Notification, Team, User};
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function send(User $user, array $data): ?Notification
    {
        $pref = $user->notificationPreference;
        if ($pref && !$pref->system_enabled) {
            return null;
        }

        return Notification::create([
            'user_id'   => $user->id,
            'type'      => $data['type'],
            'channel'   => 'system',
            'title'     => $data['title'],
            'message'   => $data['message'],
            'data_json' => $data['data_json'] ?? [],
        ]);
    }

    public function deleteNotification(Notification $notification): void
    {
        $notification->delete();
    }

    public function acceptInvitation(Notification $notification, User $user): Team
    {
        if (!$notification->isActionable()) {
            throw new \Exception("This notification cannot be accepted.");
        }

        $team = Team::findOrFail($notification->team_id);

        if ($user->teams()->exists()) {
            throw new \Exception("User is already in a team.");
        }

        DB::transaction(function () use ($notification, $team, $user) {
            $team->members()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);
            $notification->markAsRead();
        });

        return $team;
    }

    public function rejectInvitation(Notification $notification): void
    {
        if (!$notification->isActionable()) {
            throw new \Exception("This notification cannot be rejected.");
        }
        $notification->markAsRead();
    }
}
