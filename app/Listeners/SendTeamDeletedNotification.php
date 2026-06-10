<?php

namespace App\Listeners;

use App\Events\TeamDeleted;
use App\Services\NotificationService;

class SendTeamDeletedNotification
{
    public function __construct(protected NotificationService $service)
    {
    }

    public function handle(TeamDeleted $event): void
    {
        $this->service->notifyTeamDeleted($event->members, $event->teamName);
    }
}
