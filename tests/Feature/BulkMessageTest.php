<?php

namespace Tests\Feature;

use App\Jobs\SendBulkMessage;
use App\Mail\BulkMessageMail;
use App\Models\BulkMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BulkMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'student', 'mentor'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_admin_can_queue_a_bulk_message()
    {
        Bus::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/bulk-messages', [
                'target_group' => 'students',
                'subject' => 'Important update',
                'body' => 'Please check your dashboard for new tasks.',
            ]);

        $response->assertStatus(202);
        $response->assertJsonPath('bulk_message.target_group', 'students');

        $this->assertDatabaseHas('bulk_messages', [
            'sender_id' => $admin->id,
            'target_group' => 'students',
            'subject' => 'Important update',
        ]);

        $this->assertDatabaseHas('audit_events', [
            'user_id' => $admin->id,
            'action' => 'bulk_message_sent',
            'object_type' => 'bulk_message',
        ]);

        Bus::assertDispatched(SendBulkMessage::class);
    }

    public function test_non_admin_cannot_send_bulk_message()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $response = $this->actingAs($student)
            ->postJson('/api/admin/bulk-messages', [
                'target_group' => 'students',
                'subject' => 'Hello',
                'body' => 'Body text',
            ]);

        $response->assertStatus(403);
    }

    public function test_sending_job_emails_resolved_recipients_and_marks_message_as_sent()
    {
        Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $student1 = User::factory()->create();
        $student1->assignRole('student');
        $student2 = User::factory()->create();
        $student2->assignRole('student');

        $mentor = User::factory()->create();
        $mentor->assignRole('mentor');

        $bulkMessage = BulkMessage::factory()->create([
            'sender_id' => $admin->id,
            'target_group' => 'students',
            'subject' => 'Reminder',
            'body' => 'Submit your milestone report.',
            'sent_at' => null,
        ]);

        (new SendBulkMessage($bulkMessage->id))->handle();

        Mail::assertSent(BulkMessageMail::class, 2);
        Mail::assertSent(BulkMessageMail::class, fn ($mail) => $mail->hasTo($student1->email));
        Mail::assertSent(BulkMessageMail::class, fn ($mail) => $mail->hasTo($student2->email));
        Mail::assertNotSent(BulkMessageMail::class, fn ($mail) => $mail->hasTo($mentor->email));

        $bulkMessage->refresh();
        $this->assertNotNull($bulkMessage->sent_at);
        $this->assertEquals(2, $bulkMessage->recipients()->count());
        $this->assertNotNull($bulkMessage->recipients()->first()->pivot->delivered_at);
    }
}
