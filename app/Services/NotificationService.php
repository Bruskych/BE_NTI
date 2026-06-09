<?php

namespace App\Services;

use App\Mail\TemplatedNotificationMail;
use App\Models\{EmailTemplate, Notification, Team, User};
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
}
