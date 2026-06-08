<?php

namespace App\Services;

use App\Mail\TemplatedNotificationMail;
use App\Models\{Application, ApplicationAnswer, ApplicationHistory, ApplicationPairingSubmission, AuditEvent, EmailTemplate, FormField};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    const ANSWER_FILES_PATH = 'answers';
    const PAIRING_FILES_PATH = 'pairing';

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

    /**
     * Persist (or update) answers to the program/call's configurable form fields.
     */
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

    /**
     * Persist (or update) Program B pairing submissions (CV, motivation letter, solution proposal).
     */
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

    public function submitApplication(Application $application, int $userId): void
    {
        $this->ensureSubmissionRequirementsAreMet($application);

        \DB::transaction(function () use ($application, $userId) {
            $oldStatus = $application->status;
            $application->update(['status' => Application::STATUS_SUBMITTED, 'submitted_at' => now()]);

            $this->logHistory($application, $oldStatus, Application::STATUS_SUBMITTED, $userId, 'Submitted by leader.');
        });
    }

    /**
     * Spec 6.3: "validácia povinných polí a príloh pred odoslaním" — required form
     * fields and Program B pairing documents must be present before submission.
     */
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

            if ($newStatus === Application::STATUS_APPROVED) {
                $this->sendApprovalNotificationEmail($application);
            }

            return $application->fresh();
        });
    }

    /**
     * Spec 6.4: notifies the team leader using the admin-managed "project_approved" email template.
     * Silently skipped when the template hasn't been configured or the leader has no email.
     */
    private function sendApprovalNotificationEmail(Application $application): void
    {
        $template = EmailTemplate::where('name', 'project_approved')->first();
        $leader = $application->team?->leader;

        if (!$template || !$leader?->email) {
            return;
        }

        Mail::to($leader->email)->queue(new TemplatedNotificationMail($template, [
            'leader_name'   => $leader->name,
            'project_title' => $application->challenge?->title ?? $application->program?->name ?? 'your application',
        ]));
    }

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
