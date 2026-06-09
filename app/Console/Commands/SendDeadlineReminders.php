<?php

namespace App\Console\Commands;

use App\Models\Milestone;
use App\Services\NotificationService;
use Illuminate\Console\Command;

/** Консольная команда для отправки напоминаний о приближающихся дедлайнах мілстоунів (spec 6.4) */
class SendDeadlineReminders extends Command
{
    protected $signature   = 'notifications:deadline-reminders {--days=3 : За сколько дней до дедлайна отправлять напоминание}';
    protected $description = 'Send email and system notifications for milestones with approaching deadlines';

    public function handle(NotificationService $notifications): int
    {
        $days = (int) $this->option('days');

        // Знаходимо мілстоуни, дедлайн яких настає через $days днів (±0 годин)
        $milestones = Milestone::query()
            ->whereNotNull('deadline')
            ->whereDate('deadline', now()->addDays($days)->toDateString())
            ->whereNotIn('status', ['approved', 'archived'])
            ->with(['project.team.leader'])
            ->get();

        if ($milestones->isEmpty()) {
            $this->info("No deadlines in {$days} days.");
            return self::SUCCESS;
        }

        foreach ($milestones as $milestone) {
            $leader = $milestone->project?->team?->leader;
            if (!$leader) {
                continue;
            }

            $notifications->sendWithEmail(
                $leader,
                [
                    'type'      => 'milestone_deadline',
                    'title'     => 'Milestone deadline approaching',
                    'message'   => "The deadline for milestone \"{$milestone->title}\" is " . $milestone->deadline->format('Y-m-d') . '.',
                    'data_json' => ['milestone_id' => $milestone->id, 'project_id' => $milestone->project_id],
                ],
                'milestone_deadline',
                [
                    'milestone_title' => $milestone->title,
                    'deadline'        => $milestone->deadline->format('Y-m-d'),
                ]
            );

            $this->line("Notified {$leader->email} about milestone #{$milestone->id} \"{$milestone->title}\"");
        }

        $this->info("Sent reminders for {$milestones->count()} milestone(s).");
        return self::SUCCESS;
    }
}
