<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\ExportsLog;
use App\Models\Project;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\PhpWord;

/** Задание экспорта данных в форматах CSV, XLSX, PDF и DOCX с поддержкой фильтров */
class GenerateExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $exportId;

    public function __construct(int $exportId)
    {
        $this->exportId = $exportId;
    }

    /** Определяет тип экспорта и запускает соответствующий обработчик */
    public function handle()
    {
        $log = ExportsLog::find($this->exportId);
        if (!$log) {
            return;
        }

        $type = $log->export_type;
        $filename = 'exports/' . $type . '_' . $log->id . '_' . time();

        if ($type === 'personal_data_json') {
            $this->exportPersonalDataJson($log, $filename);
            return;
        }

        if (preg_match('/^(users|projects|applications)_(csv|xlsx|pdf|docx)$/', $type, $matches)) {
            $resource = $matches[1];
            $format = $matches[2];

            match ($resource) {
                'users' => $this->exportUsers($format, $filename, $log),
                'projects' => $this->exportProjects($format, $filename, $log),
                'applications' => $this->exportApplications($format, $filename, $log),
                default => null,
            };

            return;
        }

        $filename .= '.txt';
        Storage::put($filename, "Export ({$type}) generated at " . now()->toIso8601String());
        $log->file_path = $filename;
        $log->save();
    }

    /** Экспортирует пользователей в заданном формате */
    protected function exportUsers(string $format, string $filename, ExportsLog $log): void
    {
        $filters = $log->filters_json ?? [];
        $query = $this->applyFilters(User::with('roles'), 'users', $filters);

        if ($format === 'csv') {
            $filename .= '.csv';
            $handle = fopen(storage_path('app/' . $filename), 'w');
            fputcsv($handle, ['id', 'name', 'email', 'roles', 'created_at'], ',', '"', '\\');

            $query->chunk(100, function ($users) use ($handle) {
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->id,
                        $user->name,
                        $user->email,
                        implode(',', $user->getRoleNames()->toArray()),
                        $user->created_at ? $user->created_at->toIso8601String() : null,
                    ], ',', '"', '\\');
                }
            });

            fclose($handle);
            $this->saveLog($log, $filename);
            return;
        }

        if ($format === 'xlsx') {
            $filename .= '.xlsx';
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray(['id', 'name', 'email', 'roles', 'created_at'], null, 'A1');

            $row = 2;
            $query->chunk(100, function ($users) use (&$row, $sheet) {
                foreach ($users as $user) {
                    $sheet->fromArray([
                        $user->id,
                        $user->name,
                        $user->email,
                        implode(',', $user->getRoleNames()->toArray()),
                        $user->created_at ? $user->created_at->toIso8601String() : null,
                    ], null, 'A' . $row);
                    $row++;
                }
            });

            $writer = new Xlsx($spreadsheet);
            $writer->save(storage_path('app/' . $filename));
            $this->saveLog($log, $filename);
            return;
        }

        if ($format === 'pdf') {
            $filename .= '.pdf';
            $users = $query->get();
            $html = view('exports.users', ['users' => $users])->render();
            Pdf::loadHTML($html)->save(storage_path('app/' . $filename));
            $this->saveLog($log, $filename);
            return;
        }

        if ($format === 'docx') {
            $rows = [];
            $query->chunk(100, function ($users) use (&$rows) {
                foreach ($users as $user) {
                    $rows[] = [
                        $user->id,
                        $user->name,
                        $user->email,
                        implode(',', $user->getRoleNames()->toArray()),
                        $user->created_at ? $user->created_at->toIso8601String() : null,
                    ];
                }
            });

            $this->generateDocxReport('Users Report', ['id', 'name', 'email', 'roles', 'created_at'], $rows, $filename, $log);
        }
    }

    /** Экспортирует проекты в заданном формате */
    protected function exportProjects(string $format, string $filename, ExportsLog $log): void
    {
        $filters = $log->filters_json ?? [];
        $query = $this->applyFilters(Project::with('application.team'), 'projects', $filters);

        if ($format === 'csv') {
            $filename .= '.csv';
            $handle = fopen(storage_path('app/' . $filename), 'w');
            fputcsv($handle, ['id', 'title', 'status', 'final_score', 'started_at', 'finished_at', 'team'], ',', '"', '\\');

            $query->chunk(100, function ($projects) use ($handle) {
                foreach ($projects as $project) {
                    fputcsv($handle, [
                        $project->id,
                        $project->title,
                        $project->status,
                        $project->final_score,
                        $project->started_at ? $project->started_at->toIso8601String() : null,
                        $project->finished_at ? $project->finished_at->toIso8601String() : null,
                        optional($project->application->team)->name,
                    ], ',', '"', '\\');
                }
            });

            fclose($handle);
            $this->saveLog($log, $filename);
            return;
        }

        if ($format === 'xlsx') {
            $filename .= '.xlsx';
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray(['id', 'title', 'status', 'final_score', 'started_at', 'finished_at', 'team'], null, 'A1');

            $row = 2;
            $query->chunk(100, function ($projects) use (&$row, $sheet) {
                foreach ($projects as $project) {
                    $sheet->fromArray([
                        $project->id,
                        $project->title,
                        $project->status,
                        $project->final_score,
                        $project->started_at ? $project->started_at->toIso8601String() : null,
                        $project->finished_at ? $project->finished_at->toIso8601String() : null,
                        optional($project->application->team)->name,
                    ], null, 'A' . $row);
                    $row++;
                }
            });

            $writer = new Xlsx($spreadsheet);
            $writer->save(storage_path('app/' . $filename));
            $this->saveLog($log, $filename);
            return;
        }

        if ($format === 'pdf') {
            $filename .= '.pdf';
            $projects = $query->get();
            $html = view('exports.projects', ['projects' => $projects])->render();
            Pdf::loadHTML($html)->save(storage_path('app/' . $filename));
            $this->saveLog($log, $filename);
            return;
        }

        if ($format === 'docx') {
            $rows = [];
            $query->chunk(100, function ($projects) use (&$rows) {
                foreach ($projects as $project) {
                    $rows[] = [
                        $project->id,
                        $project->title,
                        $project->status,
                        $project->final_score,
                        $project->started_at ? $project->started_at->toIso8601String() : null,
                        $project->finished_at ? $project->finished_at->toIso8601String() : null,
                        optional($project->application->team)->name,
                    ];
                }
            });

            $this->generateDocxReport('Projects Report', ['id', 'title', 'status', 'final_score', 'started_at', 'finished_at', 'team'], $rows, $filename, $log);
        }
    }

    /** Экспортирует заявки в заданном формате */
    protected function exportApplications(string $format, string $filename, ExportsLog $log): void
    {
        $filters = $log->filters_json ?? [];
        $query = $this->applyFilters(Application::with('team'), 'applications', $filters);

        if ($format === 'csv') {
            $filename .= '.csv';
            $handle = fopen(storage_path('app/' . $filename), 'w');
            fputcsv($handle, ['id', 'team', 'status', 'submitted_at', 'total_score', 'decision_comment'], ',', '"', '\\');

            $query->chunk(100, function ($applications) use ($handle) {
                foreach ($applications as $application) {
                    fputcsv($handle, [
                        $application->id,
                        optional($application->team)->name,
                        $application->status,
                        $application->submitted_at ? $application->submitted_at->toIso8601String() : null,
                        $application->total_score,
                        $application->decision_comment,
                    ], ',', '"', '\\');
                }
            });

            fclose($handle);
            $this->saveLog($log, $filename);
            return;
        }

        if ($format === 'xlsx') {
            $filename .= '.xlsx';
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray(['id', 'team', 'status', 'submitted_at', 'total_score', 'decision_comment'], null, 'A1');

            $row = 2;
            $query->chunk(100, function ($applications) use (&$row, $sheet) {
                foreach ($applications as $application) {
                    $sheet->fromArray([
                        $application->id,
                        optional($application->team)->name,
                        $application->status,
                        $application->submitted_at ? $application->submitted_at->toIso8601String() : null,
                        $application->total_score,
                        $application->decision_comment,
                    ], null, 'A' . $row);
                    $row++;
                }
            });

            $writer = new Xlsx($spreadsheet);
            $writer->save(storage_path('app/' . $filename));
            $this->saveLog($log, $filename);
            return;
        }

        if ($format === 'pdf') {
            $filename .= '.pdf';
            $applications = $query->get();
            $html = view('exports.applications', ['applications' => $applications])->render();
            Pdf::loadHTML($html)->save(storage_path('app/' . $filename));
            $this->saveLog($log, $filename);
            return;
        }

        if ($format === 'docx') {
            $rows = [];
            $query->chunk(100, function ($applications) use (&$rows) {
                foreach ($applications as $application) {
                    $rows[] = [
                        $application->id,
                        optional($application->team)->name,
                        $application->status,
                        $application->submitted_at ? $application->submitted_at->toIso8601String() : null,
                        $application->total_score,
                        $application->decision_comment,
                    ];
                }
            });

            $this->generateDocxReport('Applications Report', ['id', 'team', 'status', 'submitted_at', 'total_score', 'decision_comment'], $rows, $filename, $log);
        }
    }

    /** Применяет фильтры к запросу в зависимости от типа ресурса */
    protected function applyFilters($query, string $resource, array $filters)
    {
        if (isset($filters['active'])) {
            $active = filter_var($filters['active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($active !== null) {
                if ($resource === 'users') {
                    $query->when($active, fn ($query) => $query->whereNotNull('email_verified_at'))
                        ->when(!$active, fn ($query) => $query->whereNull('email_verified_at'));
                }

                if ($resource === 'projects') {
                    $inactiveStatuses = ['archived', 'suspended'];
                    $query->when($active, fn ($query) => $query->whereNotIn('status', $inactiveStatuses))
                        ->when(!$active, fn ($query) => $query->whereIn('status', $inactiveStatuses));
                }

                if ($resource === 'applications') {
                    $inactiveStatuses = ['rejected', 'archived'];
                    $query->when($active, fn ($query) => $query->whereNotIn('status', $inactiveStatuses))
                        ->when(!$active, fn ($query) => $query->whereIn('status', $inactiveStatuses));
                }
            }
        }

        if (isset($filters['status']) && is_string($filters['status'])) {
            $status = $filters['status'];
            if ($status !== '' && $status !== 'active' && $status !== 'inactive') {
                $query->where('status', $status);
            }
        }

        return $query;
    }

    /** Экспортирует персональные данные пользователя в JSON для GDPR-запроса */
    protected function exportPersonalDataJson(ExportsLog $log, string $filename): void
    {
        $filename .= '.json';
        $userId = data_get($log->filters_json, 'user_id');
        $user = User::with([
            'studentProfile',
            'organizations',
            'teams',
            'gdprConsents',
            'auditEvents',
            'notificationPreference',
        ])->find($userId);

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'requested_for' => $userId,
            'personal_data' => null,
        ];

        if ($user) {
            $payload['personal_data'] = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->toArray(),
                    'created_at' => $user->created_at ? $user->created_at->toIso8601String() : null,
                    'avatar_url' => $user->avatar_url,
                ],
                'student_profile' => $user->studentProfile ? $user->studentProfile->toArray() : null,
                'organizations' => $user->organizations->map(function ($organization) {
                    return [
                        'id' => $organization->id,
                        'name' => $organization->name,
                        'pivot' => $organization->pivot ? $organization->pivot->toArray() : null,
                    ];
                })->toArray(),
                'teams' => $user->teams->map(function ($team) {
                    return [
                        'id' => $team->id,
                        'name' => $team->name,
                        'pivot' => $team->pivot ? $team->pivot->toArray() : null,
                    ];
                })->toArray(),
                'notification_preference' => $user->notificationPreference ? $user->notificationPreference->toArray() : null,
                'gdpr_consents' => $user->gdprConsents->toArray(),
                'audit_events' => $user->auditEvents->map(function ($event) {
                    return [
                        'action' => $event->action,
                        'object_type' => $event->object_type,
                        'object_id' => $event->object_id,
                        'old_values' => $event->old_values_json,
                        'new_values' => $event->new_values_json,
                        'result' => $event->result,
                        'created_at' => $event->created_at ? $event->created_at->toIso8601String() : null,
                    ];
                })->toArray(),
            ];
        }

        file_put_contents(storage_path('app/' . $filename), json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->saveLog($log, $filename);
    }

    /** Генерирует DOCX-отчёт с заголовком и таблицей данных */
    protected function generateDocxReport(string $title, array $headers, array $rows, string $filename, ExportsLog $log): void
    {
        $filename .= '.docx';

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addTitle($title, 1);

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80]);

        $table->addRow();
        foreach ($headers as $header) {
            $table->addCell(2200)->addText($header, ['bold' => true]);
        }

        foreach ($rows as $row) {
            $table->addRow();
            foreach ($row as $value) {
                $table->addCell(2200)->addText(htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8'));
            }
        }

        WordIOFactory::createWriter($phpWord, 'Word2007')->save(storage_path('app/' . $filename));
        $this->saveLog($log, $filename);
    }

    /** Сохраняет путь к сгенерированному файлу в записи журнала экспорта */
    protected function saveLog(ExportsLog $log, string $filename): void
    {
        $log->file_path = $filename;
        $log->save();
    }
}
