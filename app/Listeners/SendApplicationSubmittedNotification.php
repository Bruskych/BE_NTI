<?php

namespace App\Listeners;

use App\Events\ApplicationSubmitted;
use App\Services\NotificationService;

class SendApplicationSubmittedNotification
{
    public function __construct(protected NotificationService $service)
    {
    }

    public function handle(ApplicationSubmitted $event): void
    {
        $this->service->notifyApplicationSubmitted($event->application);
    }
}
