<?php

namespace App\Jobs;

use App\Models\ExportsLog;
use App\Models\User;
use App\Models\Project;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDF;

class GenerateExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $exportId;

    public function __construct(int $exportId)
    {
        $this->exportId = $exportId;
    }

    public function handle()
    {
        $log = ExportsLog::find($this->exportId);
        if (!$log) {
            return;
        }

        $type = $log->export_type;
        $filename = 'exports/' . $type . '_' . $log->id . '_' . time();

        if ($type === 'users_csv') {
            $filename .= '.csv';
            $handle = fopen(storage_path('app/' . $filename), 'w');
            fputcsv($handle, ['id', 'name', 'email', 'roles', 'created_at']);
            User::with('roles')->chunk(100, function ($users) use ($handle) {
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->id,
                        $user->name,
                        $user->email,
                        implode(',', $user->getRoleNames()->toArray()),
                        $user->created_at ? $user->created_at->toIso8601String() : null,
                    ]);
                }
            });
            fclose($handle);
            $log->file_path = $filename;
            $log->save();
            return;
        }

        if ($type === 'projects_xlsx') {
            $filename .= '.xlsx';
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray(['id', 'title', 'status', 'final_score', 'started_at', 'finished_at', 'team'], null, 'A1');

            $row = 2;
            Project::with('application.team')->chunk(100, function ($projects) use (&$row, $sheet) {
                foreach ($projects as $project) {
                    $teamName = optional($project->application->team)->name;
                    $sheet->fromArray([
                        $project->id,
                        $project->title,
                        $project->status,
                        $project->final_score,
                        $project->started_at ? $project->started_at->toIso8601String() : null,
                        $project->finished_at ? $project->finished_at->toIso8601String() : null,
                        $teamName,
                    ], null, 'A' . $row);
                    $row++;
                }
            });

            $writer = new Xlsx($spreadsheet);
            $path = storage_path('app/' . $filename);
            $writer->save($path);
            $log->file_path = $filename;
            $log->save();
            return;
        }

        if ($type === 'applications_pdf') {
            $filename .= '.pdf';
            $applications = Application::with('team')->get();
            $html = view('exports.applications', ['applications' => $applications])->render();
            $pdf = PDF::loadHTML($html);
            $path = storage_path('app/' . $filename);
            $pdf->save($path);
            $log->file_path = $filename;
            $log->save();
            return;
        }

        // Fallback
        $filename .= '.txt';
        Storage::put($filename, "Export ({$type}) generated at " . now()->toIso8601String());
        $log->file_path = $filename;
        $log->save();
    }
}
