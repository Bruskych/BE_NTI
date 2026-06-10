<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Call;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentApplicationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_student_can_register_get_approved_and_submit_an_application_with_attachments()
    {
        $program = Program::create(['name' => 'NTI Practice', 'type' => 'practice', 'is_active' => true]);
        $call = Call::factory()->create(['program_id' => $program->id, 'status' => 'open']);

        // 1. Student creates an account
        $this->postJson('/api/auth/register', [
            'name'                  => 'Ivan Student',
            'email'                 => 'ivan@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'gdpr_consent'          => true,
            'role'                  => 'student',
        ])->assertStatus(201);

        $student = User::where('email', 'ivan@example.com')->firstOrFail();
        $this->assertTrue($student->hasRole('visitor'));
        $this->assertNotNull($student->studentProfile);

        // GDPR consent (privacy policy + terms of service) is captured at registration
        $this->assertDatabaseHas('gdpr_consents', [
            'user_id'      => $student->id,
            'consent_type' => 'privacy_policy',
        ]);
        $this->assertDatabaseHas('gdpr_consents', [
            'user_id'      => $student->id,
            'consent_type' => 'terms_of_service',
        ]);

        $team = $student->teams()->where('teams.leader_id', $student->id)->firstOrFail();
        $registrationApplication = Application::where('team_id', $team->id)->firstOrFail();
        $this->assertEquals(Application::STATUS_SUBMITTED, $registrationApplication->status);

        // Program A requires a team of at least 3 members before an application can be submitted
        $team->members()->attach(User::factory()->create()->id, ['role' => 'member', 'joined_at' => now()]);
        $team->members()->attach(User::factory()->create()->id, ['role' => 'member', 'joined_at' => now()]);

        // 2. Student fills out the profile
        $this->actingAs($student)
            ->postJson('/api/settings/update-profile/name', ['name' => 'Ivan Petrenko'])
            ->assertStatus(200);

        $this->assertEquals('Ivan Petrenko', $student->fresh()->name);

        // 3. Admin reviews and approves the registration -> the "student" role is granted
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->postJson("/api/admin/students/{$registrationApplication->id}/approve", ['comment' => 'Welcome aboard'])
            ->assertStatus(200);

        $student = $student->fresh();
        $this->assertTrue($student->hasRole('student'));

        // 4. Student (team leader) opens an application for an active call
        $createResponse = $this->actingAs($student)
            ->postJson('/api/applications', [
                'program_id' => $program->id,
                'call_id'    => $call->id,
            ]);
        $createResponse->assertStatus(201);

        $application = Application::findOrFail($createResponse->json('id'));
        $this->assertEquals(Application::STATUS_DRAFT, $application->status);
        $this->assertEquals($team->id, $application->team_id);

        // 5. Student attaches a document (e.g. motivation letter) to the application
        Storage::fake('public');
        $file = UploadedFile::fake()->create('motivation-letter.pdf', 100);

        $this->actingAs($student)
            ->postJson('/api/documents', [
                'file'           => $file,
                'application_id' => $application->id,
                'type'           => 'motivation_letter',
                'classification' => 'internal',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('documents', [
            'application_id' => $application->id,
            'uploaded_by'    => $student->id,
        ]);

        // 6. Student (team leader) submits the application for review
        $submitResponse = $this->actingAs($student)
            ->postJson("/api/applications/{$application->id}/submit");

        $submitResponse->assertStatus(200);
        $this->assertEquals(Application::STATUS_SUBMITTED, $application->fresh()->status);
    }

    public function test_unapproved_visitor_cannot_create_an_application()
    {
        $program = Program::create(['name' => 'NTI Practice', 'type' => 'practice', 'is_active' => true]);
        $call = Call::factory()->create(['program_id' => $program->id, 'status' => 'open']);

        $this->postJson('/api/auth/register', [
            'name'                  => 'Olha Newbie',
            'email'                 => 'olha@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'gdpr_consent'          => true,
            'role'                  => 'student',
        ])->assertStatus(201);

        $visitor = User::where('email', 'olha@example.com')->firstOrFail();
        $this->assertTrue($visitor->hasRole('visitor'));

        $this->actingAs($visitor)
            ->postJson('/api/applications', [
                'program_id' => $program->id,
                'call_id'    => $call->id,
            ])
            ->assertStatus(403);
    }

    public function test_registration_requires_gdpr_consent()
    {
        $this->postJson('/api/auth/register', [
            'name'                  => 'No Consent',
            'email'                 => 'noconsent@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'student',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['gdpr_consent']);

        $this->assertDatabaseMissing('users', ['email' => 'noconsent@example.com']);
    }
}
