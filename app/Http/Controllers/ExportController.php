<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExportRequest;
use App\Jobs\GenerateExport;
use App\Models\AuditEvent;
use App\Models\ExportsLog;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/** Контроллер экспорта данных: постановка заданий в очередь и просмотр журнала экспортов */
class ExportController extends Controller
{
    /** Возвращает постраничный журнал экспортов */
    #[OA\Get(
        path: '/admin/exports',
        summary: '[Admin] List export logs (paginated)',
        tags: ['Exports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of export logs'),
            new OA\Response(response: 403, description: 'Not authorized to view exports'),
        ]
    )]
    public function index(Request $request)
    {
        $this->authorize('viewAny', ExportsLog::class);
        $logs = ExportsLog::latest('created_at')->paginate(20);
        return $this->apiJson($logs);
    }

    /** Возвращает доступные типы экспорта, сгруппированные по ресурсу */
    #[OA\Get(
        path: '/admin/exports/types',
        summary: '[Admin] List available export types grouped by resource',
        tags: ['Exports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Available export types'),
        ]
    )]
    public function types()
    {
        return $this->apiJson([
            'types' => StoreExportRequest::availableExportOptionsGrouped(),
        ]);
    }

    /** Ставит задание на экспорт данных в очередь */
    #[OA\Post(
        path: '/admin/exports',
        summary: '[Admin] Schedule a data export job',
        tags: ['Exports'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 202, description: 'Export scheduled'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreExportRequest $request)
    {
        $log = $this->createExportLog($request->user()->id, $request->export_type, $request->filters ?? []);

        GenerateExport::dispatch($log->id);

        return $this->apiJson([
            'message' => 'Export scheduled',
            'export'  => $log
        ], 202);
    }

    /** Ставит задание на экспорт по сокращённому маршруту ресурс/формат */
    #[OA\Post(
        path: '/admin/export/{resource}/{format}',
        summary: '[Admin] Schedule a data export job by resource and format shorthand',
        tags: ['Exports'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'resource', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'format', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 202, description: 'Export scheduled'),
            new OA\Response(response: 404, description: 'Export format not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
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

        return $this->apiJson([
            'message' => 'Export scheduled',
            'export'  => $log
        ], 202);
    }

    /** Создаёт запись журнала экспорта и аудит-событие */
    protected function createExportLog(int $userId, string $exportType, array $filters): ExportsLog
    {
        $log = ExportsLog::create([
            'user_id' => $userId,
            'export_type' => $exportType,
            'filters_json' => $filters,
            'created_at' => now(),
        ]);

        // Spec 13: "Audit log pre administratívne zmeny, rozhodnutia komisie, zmeny rolí a exporty"
        AuditEvent::create([
            'user_id'         => $userId,
            'action'          => 'export_generated',
            'object_type'     => 'exports_log',
            'object_id'       => $log->id,
            'new_values_json' => ['export_type' => $exportType, 'filters' => $filters],
            'result'          => 'success',
            'created_at'      => now(),
        ]);

        return $log;
    }
}
