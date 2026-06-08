<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotificationPolicy
{
    public function view(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    public function accept(User $user, Notification $notification): Response
    {
        if ($user->id !== $notification->user_id) {
            return Response::deny('You do not own this notification.');
        }

        if (!$notification->isActionable()) {
            return Response::deny('This notification is not an invitation.');
        }

        return Response::allow();
    }

    public function reject(User $user, Notification $notification): Response
    {
        return $this->accept($user, $notification);
    }

    public function delete(User $user, Notification $notification): Response
    {
        return $user->id === $notification->user_id
            ? Response::allow()
            : Response::deny('You do not own this notification.');
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $this->authorize('deleteAll', \App\Models\Notification::class);

        $request->user()->notifications()->delete();

        return response()->json([
            'message' => 'All notifications cleared successfully.'
        ], 200);
    }
}
