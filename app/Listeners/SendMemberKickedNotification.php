<?php

namespace App\Listeners;

use App\Events\MemberKickedFromTeam;
use App\Services\NotificationService;

class SendMemberKickedNotification
{
    public function __construct(protected NotificationService $service)
    {
    }

    public function handle(MemberKickedFromTeam $event): void
    {
        $this->service->notifyMemberKicked($event->user, $event->team);
    }
}
