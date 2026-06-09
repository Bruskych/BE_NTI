<?php

namespace App\Services;

use App\Mail\TemplatedNotificationMail;
use App\Models\{Application, ApplicationAnswer, ApplicationHistory, ApplicationPairingSubmission, AuditEvent, EmailTemplate, FormField};
use App\Services\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/** Сервис для управления жизненным циклом заявки: создание, сохранение ответов, отправка и принятие решений */
class ApplicationService
{
    public function __construct(private NotificationService $notifications) {}
    const ANSWER_FILES_PATH = 'answers';
    const PAIRING_FILES_PATH = 'pairing';

    /** Создаёт черновик заявки и фиксирует начальный статус в истории */
    public function createApplication(array $data, int $teamId, int $userId): Application
    {
        return \DB::transaction(function () use ($data, $teamId, $userId) {
            $application = Application::create(array_merge($data, [
                'team_id' => $teamId,
                'status'  => Application::STATUS_DRAFT,
            ]));

            $this->logHistory($application, null, Application::STATUS_DRAFT, $userId, 'Application created.');

            return $application;
        });
    }

    /** Сохраняет или обновляет ответы на поля формы заявки, включая загружаемые файлы */
    public function saveAnswers(Application $application, array $answers): Application
    {
        \DB::transaction(function () use ($application, $answers) {
            foreach ($answers as $answer) {
                $payload = array_filter([
                    'value_text' => $answer['value_text'] ?? null,
                    'value_json' => $answer['value_json'] ?? null,
                ], fn($value) => $value !== null);

                if (($answer['file'] ?? null) instanceof UploadedFile) {
                    $payload['file_path'] = $answer['file']->store(self::ANSWER_FILES_PATH, 'public');
                }

                ApplicationAnswer::updateOrCreate(
                    ['application_id' => $application->id, 'field_id' => $answer['field_id']],
                    $payload
                );
            }
        });

        return $application->fresh(['answers.field']);
    }

    /** Сохраняет документы парного отбора Programme B (CV, мотивационное письмо, предложение решения) */
    public function savePairingSubmissions(Application $application, array $submissions): Application
    {
        \DB::transaction(function () use ($application, $submissions) {
            foreach ($submissions as $submission) {
                $payload = array_filter([
                    'notes' => $submission['notes'] ?? null,
                ], fn($value) => $value !== null);

                if (($submission['file'] ?? null) instanceof UploadedFile) {
                    $payload['file_path'] = $submission['file']->store(self::PAIRING_FILES_PATH, 'public');
                }

                ApplicationPairingSubmission::updateOrCreate(
                    ['application_id' => $application->id, 'type' => $submission['type']],
                    $payload
                );
            }
        });

        return $application->fresh(['pairingSubmissions']);
    }

    /** Переводит заявку в статус «submitted» после проверки обязательных требований */
    public function submitApplication(Application $application, int $userId): void
    {
        $this->ensureSubmissionRequirementsAreMet($application);

        \DB::transaction(function () use ($application, $userId) {
            $oldStatus = $application->status;
            $application->update(['status' => Application::STATUS_SUBMITTED, 'submitted_at' => now()]);

            $this->logHistory($application, $oldStatus, Application::STATUS_SUBMITTED, $userId, 'Submitted by leader.');
        });

        // Підтвердження подачі заявки — відправляємо email лідеру команди
        $leader = $application->team?->leader;
        if ($leader) {
            $this->notifications->sendWithEmail(
                $leader,
                [
                    'type'      => 'application_submitted',
                    'title'     => 'Application submitted successfully',
                    'message'   => 'Your application has been submitted and is under review.',
                    'data_json' => ['application_id' => $application->id],
                ],
                'application_submitted',
                [
                    'leader_name'   => $leader->name,
                    'project_title' => $application->challenge?->title ?? $application->program?->name ?? 'your application',
                ]
            );
        }
    }

    /** Проверяет заполнение обязательных полей и документов Programme B перед отправкой */
    private function ensureSubmissionRequirementsAreMet(Application $application): void
    {
        $errors = [];

        $requiredFields = FormField::where('program_id', $application->program_id)
            ->where('required', true)
            ->where(function ($query) use ($application) {
                $query->whereNull('call_id')->orWhere('call_id', $application->call_id);
            })
            ->get();

        if ($requiredFields->isNotEmpty()) {
            $answeredFieldIds = $application->answers()
                ->where(function ($query) {
                    $query->whereNotNull('value_text')
                        ->orWhereNotNull('value_json')
                        ->orWhereNotNull('file_path');
                })
                ->pluck('field_id');

            foreach ($requiredFields as $field) {
                if (!$answeredFieldIds->contains($field->id)) {
                    $errors["answers.{$field->id}"] = ["The field \"{$field->label}\" is required before submission."];
                }
            }
        }

        if ($application->isProgramB()) {
            $submittedTypes = $application->pairingSubmissions()->pluck('type');

            foreach ([
                ApplicationPairingSubmission::TYPE_CV,
                ApplicationPairingSubmission::TYPE_MOTIVATION_LETTER,
                ApplicationPairingSubmission::TYPE_SOLUTION_PROPOSAL,
            ] as $requiredType) {
                if (!$submittedTypes->contains($requiredType)) {
                    $errors["pairing_submissions.{$requiredType}"] = ["The \"{$requiredType}\" document is required before submission."];
                }
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /** Принимает или отклоняет заявку, фиксирует аудит-событие и отправляет email при одобрении */
    public function decideApplication(Application $application, string $decision, ?string $comment, int $userId): Application
    {
        return \DB::transaction(function () use ($application, $decision, $comment, $userId) {
            $oldStatus = $application->status;
            $newStatus = $decision === 'approve' ? Application::STATUS_APPROVED : Application::STATUS_REJECTED;
            $totalScore = $application->evaluations()->avg('total_score');

            $application->update([
                'status'           => $newStatus,
                'decision_comment' => $comment,
                'total_score'      => $totalScore,
                'approved_at'      => $newStatus === Application::STATUS_APPROVED ? now() : $application->approved_at,
                'rejected_at'      => $newStatus === Application::STATUS_REJECTED ? now() : $application->rejected_at,
            ]);

            $this->logHistory($application, $oldStatus, $newStatus, $userId, $comment ?? 'Decided after evaluation.');

            AuditEvent::create([
                'user_id'         => $userId,
                'action'          => 'application_decided',
                'object_type'     => 'application',
                'object_id'       => $application->id,
                'old_values_json' => ['status' => $oldStatus],
                'new_values_json' => ['status' => $newStatus, 'decision' => $decision, 'comment' => $comment],
                'result'          => 'success',
                'created_at'      => now(),
            ]);

            $this->sendDecisionEmail($application, $newStatus, $comment);

            return $application->fresh();
        });
    }

    /** Отправляет email лидеру команды при одобрении или отклонении заявки */
    private function sendDecisionEmail(Application $application, string $newStatus, ?string $comment): void
    {
        $leader = $application->team?->leader;
        if (!$leader?->email) {
            return;
        }

        $templateName = $newStatus === Application::STATUS_APPROVED ? 'project_approved' : 'project_rejected';
        $template = EmailTemplate::where('name', $templateName)->first();

        if (!$template) {
            return;
        }

        Mail::to($leader->email)->queue(new TemplatedNotificationMail($template, [
            'leader_name'   => $leader->name,
            'project_title' => $application->challenge?->title ?? $application->program?->name ?? 'your application',
            'comment'       => $comment ?? '',
        ]));
    }

    /** Записывает строку истории изменений статуса заявки */
    private function logHistory(Application $app, ?string $old, string $new, int $userId, string $comment): void
    {
        ApplicationHistory::create([
            'application_id' => $app->id,
            'old_status'     => $old,
            'new_status'     => $new,
            'changed_by'     => $userId,
            'comment'        => $comment,
            'created_at'     => now(),
        ]);
    }
}
