<?php

use App\Console\Commands\SendDeadlineReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Консольная команда inspire — выводит вдохновляющую цитату
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ежедневная рассылка напоминаний о дедлайнах мілстоунів (spec 6.4)
// Запускается каждый день в 08:00 и отправляет уведомления за 3 дня до дедлайна
Schedule::command(SendDeadlineReminders::class, ['--days=3'])->dailyAt('08:00');
