<?php

namespace App\Listeners;

use App\Events\MilestoneChanged;
use App\Services\NotificationService;

class SendMilestoneNotification
{
    public function __construct(protected NotificationService $service)
    {
    }

    public function handle(MilestoneChanged $event): void
    {
        $this->service->notifyMilestoneStatusChanged($event->milestone, $event->action);
    }
}
