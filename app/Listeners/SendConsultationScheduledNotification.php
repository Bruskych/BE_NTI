<?php

namespace App\Listeners;

use App\Events\ConsultationScheduled;
use App\Services\NotificationService;

class SendConsultationScheduledNotification
{
    public function __construct(protected NotificationService $service)
    {
    }

    public function handle(ConsultationScheduled $event): void
    {
        $this->service->notifyConsultationScheduled($event->consultation);
    }
}
