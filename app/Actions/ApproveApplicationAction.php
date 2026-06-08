<?php
// app/Actions/ApproveApplicationAction.php
namespace App\Actions;

use App\Models\Application;
use App\Models\ApplicationHistory;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class ApproveApplicationAction
{
    public function execute(Application $application, string $comment, string $role): void
    {
        DB::transaction(function () use ($application, $comment, $role) {
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
                'changed_by'     => auth()->id(),
                'comment'        => $comment,
            ]);

            $owner = $application->team->leader;
            if ($owner) {
                $owner->syncRoles([$role]);

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
