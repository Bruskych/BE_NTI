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

    public function store(StoreExportRequest $request)
    {
        $user = $request->user();

        $log = ExportsLog::create([
            'user_id'     => $user->id,
            'export_type' => $request->export_type,
            'filters_json'=> $request->filters ?? [],
            'created_at'  => now(),
        ]);

        GenerateExport::dispatch($log->id);

        return response()->json([
            'message' => 'Export scheduled',
            'export'  => $log
        ], 202);
    }
}
