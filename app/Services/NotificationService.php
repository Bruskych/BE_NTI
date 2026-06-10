<?php

namespace App\Services;

use App\Mail\TemplatedNotificationMail;
use App\Models\{EmailTemplate, Notification, Team, User, Mentorship, Consultation, Application, Milestone};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/** Сервис системных уведомлений: отправка, удаление и обработка приглашений в команду */
class NotificationService
{
    /** Создаёт системное уведомление, если пользователь не отключил системный канал */
    public function send(User $user, array $data): ?Notification
    {
        $pref = $user->notificationPreference;
        if ($pref && !$pref->system_enabled) {
            return null;
        }

        return Notification::create([
            'user_id'   => $user->id,
            'type'      => $data['type'],
            'channel'   => 'system',
            'title'     => $data['title'],
            'message'   => $data['message'],
            'data_json' => $data['data_json'] ?? [],
        ]);
    }

    /**
     * Создаёт системное уведомление И отправляет email через шаблон, если пользователь не отключил email-канал.
     * Шаблон ищется по имени ($templateName); при отсутствии email всё равно отправляется с title/message.
     */
    public function sendWithEmail(User $user, array $data, string $templateName, array $vars = []): ?Notification
    {
        $notification = $this->send($user, $data);

        $pref = $user->notificationPreference;
        if ($pref && !$pref->email_enabled) {
            return $notification;
        }

        $template = EmailTemplate::where('name', $templateName)->first();
        if ($template) {
            Mail::to($user->email)->queue(new TemplatedNotificationMail($template, array_merge([
                'user_name' => $user->name,
            ], $vars)));
        }

        return $notification;
    }

    /** Удаляет уведомление */
    public function deleteNotification(Notification $notification): void
    {
        $notification->delete();
    }

    /** Принимает приглашение в команду: добавляет пользователя и помечает уведомление как прочитанное */
    public function acceptInvitation(Notification $notification, User $user): Team
    {
        if (!$notification->isActionable()) {
            throw new \Exception("This notification cannot be accepted.");
        }

        $team = Team::findOrFail($notification->team_id);

        if ($user->teams()->exists()) {
            throw new \Exception("User is already in a team.");
        }

        DB::transaction(function () use ($notification, $team, $user) {
            $team->members()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);

            $notification->markAsRead();
        });

        event(new \App\Events\MemberJoinedTeam($user, $team));

        return $team;
    }

    /** Отклоняет приглашение в команду, помечая уведомление как прочитанное */
    public function rejectInvitation(Notification $notification): void
    {
        if (!$notification->isActionable()) {
            throw new \Exception("This notification cannot be rejected.");
        }
        $notification->markAsRead();
    }


    // УВЕДОМЛЕНИЕ ЧЕРЕЗ ИВЕНТ-ЛИСЕНЕРЫ
    public function notifyMemberJoined(User $newMember, Team $team): void
    {
        foreach ($team->members()->where('users.id', '!=', $newMember->id)->get() as $member) {
            $this->send($member, [
                'type'    => 'team_member_joined',
                'title'   => 'New member',
                'message' => "User {$newMember->name} has joined the team.",
                'data_json' => ['team_id' => $team->id]
            ]);
        }
    }

    public function notifyMemberLeft(User $leaver, Team $team): void
    {
        foreach ($team->members as $member) {
            $this->send($member, [
                'type'    => 'team_member_left',
                'title'   => 'Member left',
                'message' => "User {$leaver->name} has left the team.",
                'data_json' => ['team_id' => $team->id]
            ]);
        }
    }

    public function notifyMemberKicked(User $kickedUser, Team $team): void
    {
        $this->send($kickedUser, [
            'type'    => 'kicked_from_team',
            'title'   => 'You were kicked',
            'message' => "You have been removed from team {$team->name}.",
            'data_json' => ['team_id' => $team->id]
        ]);

        foreach ($team->members()->where('users.id', '!=', $kickedUser->id)->get() as $member) {
            $this->send($member, [
                'type'    => 'team_member_kicked',
                'title'   => 'Member removed',
                'message' => "User {$kickedUser->name} was removed from the team.",
                'data_json' => ['team_id' => $team->id]
            ]);
        }
    }

    public function notifyTeamDeleted(Collection $members, string $teamName): void
    {
        foreach ($members as $member) {
            $this->send($member, [
                'type'    => 'team_deleted',
                'title'   => 'Team deleted',
                'message' => "The team '{$teamName}' has been deleted.",
                'data_json' => []
            ]);
        }
    }

    public function notifyMentorAssigned(Mentorship $mentorship): void
    {
        $mentorship->load(['mentor', 'project.application.team.members']);
        $project = $mentorship->project;
        $mentor = $mentorship->mentor;

        // Уведомляем ментора
        if ($mentor) {
            $this->sendWithEmail($mentor, [
                'type'    => 'mentor_assigned',
                'title'   => 'New Mentorship',
                'message' => "You have been assigned to project: {$project->title}",
                'data_json' => ['mentorship_id' => $mentorship->id]
            ], 'mentor_assigned', ['project_title' => $project->title]);
        }

        // Уведомляем участников команды
        if ($project->application && $project->application->team) {
            foreach ($project->application->team->members as $member) {
                $this->send($member, [
                    'type'    => 'mentor_assigned_to_team',
                    'title'   => 'Mentor assigned',
                    'message' => "A mentor ({$mentor->name}) has been assigned to your project: {$project->title}",
                    'data_json' => ['project_id' => $project->id]
                ]);
            }
        }
    }

    public function notifyConsultationScheduled(Consultation $consultation): void
    {
        $consultation->load(['mentorship.project.application.team.members', 'mentor']);

        $teamMembers = $consultation->mentorship->project?->application?->team?->members;

        if (!$teamMembers) return;

        foreach ($teamMembers as $member) {
            $this->send($member, [
                'type'      => 'consultation_scheduled',
                'title'     => 'New Consultation',
                'message'   => "Mentor {$consultation->mentor->name} scheduled a meeting for {$consultation->scheduled_at->format('d.m.Y H:i')}",
                'data_json' => [
                    'consultation_id' => $consultation->id,
                    'mentorship_id'   => $consultation->mentorship_id
                ]
            ]);
        }
    }

    public function notifyApplicationSubmitted(Application $application): void
    {
        // Находим админов или членов комитета и шлем им уведомление
        $admins = \App\Models\User::role('admin')->get();

        foreach ($admins as $admin) {
            $this->send($admin, [
                'type'      => 'application_submitted',
                'title'     => 'New Application Received',
                'message'   => "Team '{$application->team->name}' has submitted a new application.",
                'data_json' => ['application_id' => $application->id]
            ]);
        }
    }

    public function notifyApplicationDecision(Application $application, string $decision): void
    {
        // Уведомляем лидера команды (или всю команду)
        $teamMembers = $application->team->members;
        $message = "Your application has been: " . strtoupper($decision);

        foreach ($teamMembers as $member) {
            $this->send($member, [
                'type'      => 'application_decided',
                'title'     => 'Application Update',
                'message'   => $message,
                'data_json' => ['application_id' => $application->id, 'decision' => $decision]
            ]);
        }
    }

    public function notifyMilestoneStatusChanged(Milestone $milestone, string $action): void
    {
        $milestone->load(['project.application.team.members']);
        $team = $milestone->project?->application?->team;

        if (!$team) return;

        $title = match($action) {
            'created'  => 'New Milestone Assigned',
            'updated'  => 'Milestone Updated',
            'approved' => 'Milestone Approved!',
            default    => 'Milestone Update',
        };

        $message = match($action) {
            'created'  => "A new milestone '{$milestone->title}' was added to your project.",
            'updated'  => "The milestone '{$milestone->title}' has been updated.",
            'approved' => "Great news! Milestone '{$milestone->title}' has been approved.",
            default    => "Milestone '{$milestone->title}' status changed.",
        };

        foreach ($team->members as $member) {
            $this->send($member, [
                'type'      => 'milestone_update',
                'title'     => $title,
                'message'   => $message,
                'data_json' => ['milestone_id' => $milestone->id, 'project_id' => $milestone->project_id]
            ]);
        }
    }
}
