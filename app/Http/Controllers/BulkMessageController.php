<?php

namespace App\Http\Controllers;

use App\Http\Resources\BulkMessageResource;
use App\Models\BulkMessage;
use App\Services\BulkMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use OpenApi\Attributes as OA;

/** Контроллер массовой рассылки сообщений для администраторов */
class BulkMessageController extends Controller implements HasMiddleware
{
    protected BulkMessageService $bulkMessageService;

    public function __construct(BulkMessageService $bulkMessageService)
    {
        $this->bulkMessageService = $bulkMessageService;
    }

    public static function middleware(): array
    {
        return static::resourcePolicyMiddleware(BulkMessage::class, 'bulk_message');
    }

    /** Возвращает постраничный список массовых рассылок */
    #[OA\Get(
        path: '/admin/bulk-messages',
        summary: '[Admin] List bulk messages (paginated) with recipient counts and sender',
        tags: ['Bulk Messages'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of bulk messages'),
            new OA\Response(response: 403, description: 'Not authorized to view bulk messages'),
        ]
    )]
    public function index(): JsonResponse
    {
        $messages = BulkMessage::query()
            ->withCount('recipients')
            ->with('sender')
            ->latest('created_at')
            ->paginate(20);

        return $this->apiJson(BulkMessageResource::collection($messages));
    }

    /** Создаёт и ставит в очередь массовую рассылку для указанной группы получателей */
    #[OA\Post(
        path: '/admin/bulk-messages',
        summary: '[Admin] Queue a bulk message for sending to a target group',
        tags: ['Bulk Messages'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 202, description: 'Bulk message queued for sending'),
            new OA\Response(response: 403, description: 'Not authorized to create bulk messages'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_group' => 'required|string|in:all,students,mentors,companies,team_leaders,evaluators,admins',
            'subject'      => 'required|string|max:255',
            'body'         => 'required|string',
        ]);

        $bulkMessage = $this->bulkMessageService->create($request->user(), $validated);

        return $this->apiJson([
            'message' => 'Bulk message queued for sending',
            'bulk_message' => new BulkMessageResource($bulkMessage),
        ], 202);
    }

    /** Возвращает детали одной массовой рассылки с данными о доставке */
    #[OA\Get(
        path: '/admin/bulk-messages/{bulk_message}',
        summary: '[Admin] Get a single bulk message with recipient counts and sender',
        tags: ['Bulk Messages'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'bulk_message', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Bulk message detail'),
            new OA\Response(response: 403, description: 'Not authorized to view this bulk message'),
            new OA\Response(response: 404, description: 'Bulk message not found'),
        ]
    )]
    public function show(BulkMessage $bulkMessage): JsonResponse
    {
        return $this->apiJson(new BulkMessageResource(
            $bulkMessage->loadCount('recipients')->load('sender')
        ));
    }
}
