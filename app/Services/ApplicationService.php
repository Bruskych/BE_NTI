<?php

namespace App\Services;

use App\Models\{Application, ApplicationHistory};

class ApplicationService
{
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

    public function submitApplication(Application $application, int $userId): void
    {
        \DB::transaction(function () use ($application, $userId) {
            $oldStatus = $application->status;
            $application->update(['status' => Application::STATUS_SUBMITTED, 'submitted_at' => now()]);

            $this->logHistory($application, $oldStatus, Application::STATUS_SUBMITTED, $userId, 'Submitted by leader.');
        });
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
