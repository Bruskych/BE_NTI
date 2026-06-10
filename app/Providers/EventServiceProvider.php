<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\MemberJoinedTeam::class => [
            \App\Listeners\SendTeamJoinedNotification::class,
        ],
        \App\Events\MemberLeftTeam::class => [
            \App\Listeners\SendMemberLeftNotification::class,
        ],
        \App\Events\MemberKickedFromTeam::class => [
            \App\Listeners\SendMemberKickedNotification::class,
        ],
        \App\Events\TeamDeleted::class => [
            \App\Listeners\SendTeamDeletedNotification::class,
        ],
        \App\Events\MentorAssigned::class => [
            \App\Listeners\SendMentorAssignedNotification::class,
        ],
        \App\Events\ConsultationScheduled::class => [
            \App\Listeners\SendConsultationScheduledNotification::class,
        ],
        \App\Events\ApplicationSubmitted::class => [
            \App\Listeners\SendApplicationSubmittedNotification::class,
        ],
        \App\Events\ApplicationDecided::class => [
            \App\Listeners\SendApplicationDecisionNotification::class,
        ],
        \App\Events\MilestoneChanged::class => [
            \App\Listeners\SendMilestoneNotification::class,
        ],
    ];
}
