<?php


namespace App\Actions;

use App\Models\Application;
use App\Models\ApplicationHistory;
use App\Models\AuditEvent;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

/** Действие отклонения заявки: устанавливает статус rejected, фиксирует историю и уведомляет лидера */
class RejectApplicationAction
{
    public function __construct(private NotificationService $notifications) {}

    /** Отклоняет заявку, создаёт записи истории, аудита, системного уведомления и email */
    public function execute(Application $application, string $comment, ?int $changedBy = null): void
    {
        DB::transaction(function () use ($application, $comment, $changedBy) {
            $application->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'decision_comment' => $comment,
            ]);

            ApplicationHistory::create([
                'application_id' => $application->id,
                'old_status' => 'submitted',
                'new_status' => 'rejected',
                'changed_by' => $changedBy,
                'comment' => $comment,
            ]);

            AuditEvent::create([
                'user_id'         => $changedBy,
                'action'          => $application->organization_id ? 'company_application_rejected' : 'student_application_rejected',
                'object_type'     => 'application',
                'object_id'       => $application->id,
                'old_values_json' => ['status' => 'submitted'],
                'new_values_json' => ['status' => 'rejected', 'comment' => $comment],
                'result'          => 'success',
                'created_at'      => now(),
            ]);

            $owner = $application->team ? $application->team->leader : null;
            if ($owner) {
                $this->notifications->sendWithEmail(
                    $owner,
                    [
                        'type'      => $application->organization_id ? 'company_registration_rejected' : 'student_application_rejected',
                        'title'     => 'Application rejected',
                        'message'   => 'Your application has been rejected. Reason: ' . $comment,
                        'data_json' => ['application_id' => $application->id],
                    ],
                    'application_rejected',
                    ['comment' => $comment, 'application_id' => $application->id]
                );
            }
        });
    }
}
