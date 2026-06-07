<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExportRequest;
use App\Jobs\GenerateExport;
use App\Models\ExportsLog;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ExportsLog::class);
        $logs = ExportsLog::latest('created_at')->paginate(20);
        return response()->json($logs);
    }

    public function types()
    {
        return response()->json([
            'types' => StoreExportRequest::availableExportOptionsGrouped(),
        ]);
    }

    public function store(StoreExportRequest $request)
    {
        $log = $this->createExportLog($request->user()->id, $request->export_type, $request->filters ?? []);

        GenerateExport::dispatch($log->id);

        return response()->json([
            'message' => 'Export scheduled',
            'export'  => $log
        ], 202);
    }

    public function storeByRoute(Request $request, string $resource, string $format)
    {
        $exportType = sprintf('%s_%s', $resource, $format);

        if (!in_array($exportType, StoreExportRequest::allowedExportTypes(), true)) {
            abort(404, 'Export format not found.');
        }

        $validated = $request->validate([
            'filters' => 'nullable|array',
        ]);

        $log = $this->createExportLog($request->user()->id, $exportType, $validated['filters'] ?? []);
        GenerateExport::dispatch($log->id);

        return response()->json([
            'message' => 'Export scheduled',
            'export'  => $log
        ], 202);
    }

    protected function createExportLog(int $userId, string $exportType, array $filters): ExportsLog
    {
        return ExportsLog::create([
            'user_id' => $userId,
            'export_type' => $exportType,
            'filters_json' => $filters,
            'created_at' => now(),
        ]);
    }
}
