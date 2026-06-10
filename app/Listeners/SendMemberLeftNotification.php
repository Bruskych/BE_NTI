<?php

namespace App\Listeners;

use App\Events\MemberLeftTeam;
use App\Services\NotificationService;

class SendMemberLeftNotification
{
    public function __construct(protected NotificationService $service)
    {
    }

    public function handle(MemberLeftTeam $event): void
    {
        $this->service->notifyMemberLeft($event->user, $event->team);
    }
}
