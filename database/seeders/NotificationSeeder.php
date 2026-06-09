<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

use App\Models\NotificationPreference;
use App\Models\EmailTemplate;
use App\Models\Notification;
use App\Models\BulkMessage;
use App\Models\User;

/**
 * Сидер уведомлений: создаёт системные уведомления, настройки, шаблоны писем и массовые рассылки.
 * Зависит от UserSeeder — привязывается ко всем существующим пользователям.
 */
class NotificationSeeder extends Seeder
{
    /**
     * Уведомления
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('bulk_message_recipients')->truncate();
        BulkMessage::truncate();
        EmailTemplate::truncate();
        NotificationPreference::truncate();
        Notification::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        EmailTemplate::create([
            'name'           => 'application_submitted',
            'subject'        => 'Your application has been submitted',
            'body'           => 'Dear {{ leader_name }}, your application for "{{ project_title }}" has been successfully submitted and is now under review.',
            'variables_json' => ['leader_name', 'project_title'],
        ]);
        EmailTemplate::create([
            'name'           => 'project_approved',
            'subject'        => 'Your project application has been approved!',
            'body'           => 'Dear {{ leader_name }}, your application for "{{ project_title }}" has been approved. Comment: {{ comment }}',
            'variables_json' => ['leader_name', 'project_title', 'comment'],
        ]);
        EmailTemplate::create([
            'name'           => 'project_rejected',
            'subject'        => 'Update on your project application',
            'body'           => 'Dear {{ leader_name }}, unfortunately your application for "{{ project_title }}" was not approved. Reason: {{ comment }}',
            'variables_json' => ['leader_name', 'project_title', 'comment'],
        ]);
        EmailTemplate::create([
            'name'           => 'application_approved',
            'subject'        => 'Your application has been approved!',
            'body'           => 'Dear {{ user_name }}, congratulations! Your application (ID: {{ application_id }}) has been approved. Comment: {{ comment }}',
            'variables_json' => ['user_name', 'application_id', 'comment'],
        ]);
        EmailTemplate::create([
            'name'           => 'application_rejected',
            'subject'        => 'Update on your application',
            'body'           => 'Dear {{ user_name }}, your application (ID: {{ application_id }}) was not approved. Reason: {{ comment }}',
            'variables_json' => ['user_name', 'application_id', 'comment'],
        ]);
        EmailTemplate::create([
            'name'           => 'mentor_assigned',
            'subject'        => 'You have been assigned as a mentor',
            'body'           => 'Dear {{ user_name }}, you have been assigned as a mentor for the project "{{ project_title }}".',
            'variables_json' => ['user_name', 'project_title'],
        ]);
        EmailTemplate::create([
            'name'           => 'milestone_deadline',
            'subject'        => 'The stage deadline is approaching',
            'body'           => 'We remind you that the deadline for stage "{{ milestone_title }}" is {{ deadline }}.',
            'variables_json' => ['milestone_title', 'deadline'],
        ]);

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        $users = User::all();
        foreach ($users as $user) {
            NotificationPreference::factory()->create([
                'user_id' => $user->id,
            ]);
            Notification::factory()->count(rand(15, 30))->create([
                'user_id' => $user->id,
            ]);
        }
        $bulkMessages = BulkMessage::factory()->count(3)->create();
        foreach ($bulkMessages as $message) {
            if ($message->isSent()) {
                $recipients = $users->random(rand(2, min(5, $users->count())));
                foreach ($recipients as $recipient) {
                    $message->recipients()->attach($recipient->id, [
                        'delivered_at' => fake()->dateTimeBetween($message->sent_at, 'now'),
                    ]);
                }
            }
        }
    }
}
