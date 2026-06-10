<?php

namespace App\Listeners;

use App\Events\MemberJoinedTeam;
use App\Services\NotificationService;

class SendTeamJoinedNotification
{
    public function __construct(protected NotificationService $service)
    {
    }

    public function handle(MemberJoinedTeam $event): void
    {
        $this->service->notifyMemberJoined($event->user, $event->team);
    }
}
