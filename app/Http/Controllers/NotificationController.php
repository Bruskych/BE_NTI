<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TeamResource;
use OpenApi\Attributes as OA;

/** Контроллер уведомлений: просмотр, принятие/отклонение приглашений и удаление */
class NotificationController extends Controller
{
    /** Возвращает непрочитанные уведомления текущего пользователя */
    #[OA\Get(
        path: '/notifications',
        summary: 'List the current user\'s unread notifications',
        tags: ['Notifications'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of unread notifications'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::forUser($request->user()->id)
            ->unread()
            ->latest()
            ->get();

        return $this->apiJson(['data' => $notifications]);
    }

    /** Принимает приглашение в команду из уведомления */
    #[OA\Post(
        path: '/notifications/{notification}/accept',
        summary: 'Accept a team invitation notification',
        tags: ['Notifications'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'notification', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Joined the team successfully'),
            new OA\Response(response: 403, description: 'Not authorized to accept this notification'),
            new OA\Response(response: 422, description: 'Could not accept the invitation'),
        ]
    )]
    public function accept(Notification $notification, NotificationService $service): JsonResponse
    {
        $this->authorize('accept', $notification);

        try {
            $team = $service->acceptInvitation($notification, auth()->user());
            return $this->apiJson(['message' => 'Joined successfully.', 'data' => new TeamResource($team)]);
        } catch (\Exception $e) {
            return $this->apiJson(['message' => $e->getMessage()], 422);
        }
    }

    /** Отклоняет приглашение в команду из уведомления */
    #[OA\Post(
        path: '/notifications/{notification}/reject',
        summary: 'Decline a team invitation notification',
        tags: ['Notifications'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'notification', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Declined successfully'),
            new OA\Response(response: 403, description: 'Not authorized to reject this notification'),
            new OA\Response(response: 422, description: 'Could not decline the invitation'),
        ]
    )]
    public function reject(Notification $notification, NotificationService $service): JsonResponse
    {
        $this->authorize('reject', $notification);

        try {
            $service->rejectInvitation($notification);
            return $this->apiJson(['message' => 'Declined successfully.']);
        } catch (\Exception $e) {
            return $this->apiJson(['message' => $e->getMessage()], 422);
        }
    }

    /** Удаляет одно уведомление */
    #[OA\Delete(
        path: '/notifications/{notification}',
        summary: 'Delete a single notification',
        tags: ['Notifications'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'notification', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Notification deleted'),
            new OA\Response(response: 403, description: 'Not authorized to delete this notification'),
        ]
    )]
    public function destroy(Notification $notification, NotificationService $service): JsonResponse
    {
        $this->authorize('delete', $notification);

        $service->deleteNotification($notification);

        return $this->apiJson(['message' => 'Notification deleted successfully.']);
    }

    /** Удаляет все уведомления текущего пользователя */
    #[OA\Delete(
        path: '/notifications',
        summary: 'Clear all of the current user\'s notifications',
        tags: ['Notifications'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'All notifications cleared'),
        ]
    )]
    public function destroyAll(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return $this->apiJson([
            'message' => 'All notifications cleared successfully.'
        ], 200);
    }
}
