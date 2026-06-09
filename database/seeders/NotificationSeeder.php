<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'name'              => 'project_approved',
            'subject'           => 'Your project application has been approved!',
            'body'              => 'Dear {{ leader_name }}, your request on the topic "{{ project_title }}" has been successfully moderated.',
            'variables_json'    => ['leader_name', 'project_title'],
        ]);
        EmailTemplate::create([
            'name'              => 'milestone_deadline',
            'subject'           => 'The stage deadline is approaching',
            'body'              => 'We remind you that the deadline for stage "{{ milestone_title }}" is {{ deadline }}.',
            'variables_json'    => ['milestone_title', 'deadline'],
        ]);

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        $users = User::all();
        foreach ($users as $user) {
            NotificationPreference::factory()->create([
                'user_id' => $user->id,
            ]);
            Notification::factory()->count(rand(2, 5))->create([
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
