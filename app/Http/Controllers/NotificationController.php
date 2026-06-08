<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TeamResource;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::forUser($request->user()->id)
            ->unread()
            ->latest()
            ->get();

        return response()->json(['data' => $notifications]);
    }

    public function accept(Notification $notification, NotificationService $service): JsonResponse
    {
        $this->authorize('accept', $notification);

        try {
            $team = $service->acceptInvitation($notification, auth()->user());
            return response()->json(['message' => 'Joined successfully.', 'data' => new TeamResource($team)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(Notification $notification, NotificationService $service): JsonResponse
    {
        $this->authorize('reject', $notification);

        try {
            $service->rejectInvitation($notification);
            return response()->json(['message' => 'Declined successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Notification $notification, NotificationService $service): JsonResponse
    {
        $this->authorize('delete', $notification);

        $service->deleteNotification($notification);

        return response()->json(['message' => 'Notification deleted successfully.']);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return response()->json([
            'message' => 'All notifications cleared successfully.'
        ], 200);
    }
}
