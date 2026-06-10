<?php

namespace App\Listeners;

use App\Events\MentorAssigned;
use App\Services\NotificationService;

class SendMentorAssignedNotification
{
    public function __construct(protected NotificationService $service) {}

    public function handle(MentorAssigned $event): void
    {
        $this->service->notifyMentorAssigned($event->mentorship);
    }
}
