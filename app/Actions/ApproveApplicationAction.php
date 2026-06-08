<?php
// app/Actions/ApproveApplicationAction.php
namespace App\Actions;

use App\Models\Application;
use App\Models\ApplicationHistory;
use App\Models\AuditEvent;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class ApproveApplicationAction
{
    public function execute(Application $application, string $comment, string $role, ?int $changedBy = null): void
    {
        DB::transaction(function () use ($application, $comment, $role, $changedBy) {
            $application->update([
                'status' => 'approved',
                'approved_at' => now(),
                'decision_comment' => $comment,
            ]);

            if ($application->organization_id) {
                $application->organization->update(['status' => 'active']);
            }

            ApplicationHistory::create([
                'application_id' => $application->id,
                'old_status'     => 'submitted',
                'new_status'     => 'approved',
                'changed_by'     => $changedBy,
                'comment'        => $comment,
            ]);

            AuditEvent::create([
                'user_id'         => $changedBy,
                'action'          => $role === 'student' ? 'student_application_approved' : 'company_application_approved',
                'object_type'     => 'application',
                'object_id'       => $application->id,
                'old_values_json' => ['status' => 'submitted'],
                'new_values_json' => ['status' => 'approved', 'comment' => $comment],
                'result'          => 'success',
                'created_at'      => now(),
            ]);

            $owner = $application->team->leader;
            if ($owner) {
                $oldRoles = $owner->getRoleNames()->toArray();
                $owner->syncRoles([$role]);

                AuditEvent::create([
                    'user_id'         => $changedBy,
                    'action'          => 'role_changed',
                    'object_type'     => 'user',
                    'object_id'       => $owner->id,
                    'old_values_json' => ['roles' => $oldRoles],
                    'new_values_json' => ['roles' => [$role]],
                    'result'          => 'success',
                    'created_at'      => now(),
                ]);

                Notification::create([
                    'user_id' => $owner->id,
                    'type'    => $role === 'student' ? 'student_application_approved' : 'company_registration_approved',
                    'channel' => 'system',
                    'title'   => 'Application approved ✅',
                    'message' => 'Your application has been approved. Comment: ' . $comment,
                    'data_json' => json_encode(['application_id' => $application->id]),
                ]);
            }
        });
    }
}
