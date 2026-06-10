<?php

namespace App\Listeners;

use App\Events\ApplicationDecided;
use App\Services\NotificationService;

class SendApplicationDecisionNotification
{
    public function __construct(protected NotificationService $service)
    {
    }

    public function handle(ApplicationDecided $event): void
    {
        $this->service->notifyApplicationDecision($event->application, $event->decision);
    }
}
