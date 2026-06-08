<?php

namespace App\Http\Controllers;

use App\Http\Resources\BulkMessageResource;
use App\Models\BulkMessage;
use App\Services\BulkMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BulkMessageController extends Controller
{
    protected BulkMessageService $bulkMessageService;

    public function __construct(BulkMessageService $bulkMessageService)
    {
        $this->bulkMessageService = $bulkMessageService;
        $this->authorizeResource(BulkMessage::class, 'bulk_message');
    }

    /**
     * List bulk messages
     */
    public function index(): JsonResponse
    {
        $messages = BulkMessage::query()
            ->withCount('recipients')
            ->with('sender')
            ->latest('created_at')
            ->paginate(20);

        return response()->api(BulkMessageResource::collection($messages));
    }

    /**
     * Create and queue a bulk message for sending
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_group' => 'required|string|in:all,students,mentors,companies,team_leaders,evaluators,admins',
            'subject'      => 'required|string|max:255',
            'body'         => 'required|string',
        ]);

        $bulkMessage = $this->bulkMessageService->create($request->user(), $validated);

        return response()->api([
            'message' => 'Bulk message queued for sending',
            'bulk_message' => new BulkMessageResource($bulkMessage),
        ], 202);
    }

    /**
     * Get single bulk message with delivery details
     */
    public function show(BulkMessage $bulkMessage): JsonResponse
    {
        return response()->api(new BulkMessageResource(
            $bulkMessage->loadCount('recipients')->load('sender')
        ));
    }
}
