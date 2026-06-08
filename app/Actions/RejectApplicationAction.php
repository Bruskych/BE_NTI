<?php


namespace App\Actions;

use App\Models\Application;
use App\Models\ApplicationHistory;
use App\Models\AuditEvent;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class RejectApplicationAction
{
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
                Notification::create([
                    'user_id' => $owner->id,
                    'type' => $application->organization_id ? 'company_registration_rejected' : 'student_application_rejected',
                    'channel' => 'system',
                    'title' => 'Application rejected ❌',
                    'message' => 'Your application has been rejected. Reason: ' . $comment,
                    'data_json' => json_encode(['application_id' => $application->id]),
                ]);
            }
        });
    }
}
