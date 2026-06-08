<?php

namespace Tests\Feature;

use App\Mail\TemplatedNotificationMail;
use App\Models\Application;
use App\Models\EmailTemplate;
use App\Models\Program;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_admin_can_manage_email_templates()
    {
        $admin = $this->admin();

        $createResponse = $this->actingAs($admin)
            ->postJson('/api/admin/email-templates', [
                'name'           => 'welcome_email',
                'subject'        => 'Welcome to NTI!',
                'body'           => 'Hi {{ name }}, welcome aboard.',
                'variables_json' => ['name'],
            ]);
        $createResponse->assertStatus(201)->assertJsonPath('name', 'welcome_email');

        $templateId = $createResponse->json('id');

        $this->actingAs($admin)
            ->getJson('/api/admin/email-templates')
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'welcome_email']);

        $this->actingAs($admin)
            ->putJson("/api/admin/email-templates/{$templateId}", ['subject' => 'Welcome aboard, NTI!'])
            ->assertStatus(200)
            ->assertJsonPath('subject', 'Welcome aboard, NTI!');

        $this->actingAs($admin)
            ->deleteJson("/api/admin/email-templates/{$templateId}")
            ->assertStatus(204);

        $this->assertSoftDeleted('email_templates', ['id' => $templateId]);
    }

    public function test_non_admin_cannot_manage_email_templates()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($student)
            ->getJson('/api/admin/email-templates')
            ->assertStatus(403);
    }

    public function test_approving_an_application_sends_the_project_approved_template_to_the_team_leader()
    {
        Mail::fake();

        $template = EmailTemplate::create([
            'name'           => 'project_approved',
            'subject'        => 'Your project application has been approved!',
            'body'           => 'Dear {{ leader_name }}, your application for "{{ project_title }}" has been approved.',
            'variables_json' => ['leader_name', 'project_title'],
        ]);

        $program = Program::create(['name' => 'NTI Grant Program', 'type' => 'grant', 'is_active' => true]);

        $leader = User::factory()->create(['email' => 'leader@example.com']);
        $leader->assignRole('student');

        $team = Team::create(['name' => 'Team ' . $leader->name, 'leader_id' => $leader->id, 'status' => 'active']);
        $team->members()->attach($leader->id, ['role' => 'leader', 'joined_at' => now()]);

        $application = Application::create([
            'program_id'   => $program->id,
            'team_id'      => $team->id,
            'status'       => Application::STATUS_IN_EVALUATION,
            'submitted_at' => now(),
        ]);

        $evaluator = User::factory()->create();
        $evaluator->assignRole('evaluator');

        $this->actingAs($evaluator)
            ->postJson("/api/applications/{$application->id}/decide", ['decision' => 'approve'])
            ->assertStatus(200);

        Mail::assertQueued(TemplatedNotificationMail::class, function (TemplatedNotificationMail $mail) use ($template, $leader) {
            return $mail->template->is($template)
                && $mail->hasTo($leader->email)
                && $mail->variables['leader_name'] === $leader->name
                && $mail->variables['project_title'] === 'NTI Grant Program';
        });
    }
}
