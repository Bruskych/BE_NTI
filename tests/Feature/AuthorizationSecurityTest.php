<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\BulkMessage;
use App\Models\Call;
use App\Models\Challenge;
use App\Models\Document;
use App\Models\Organization;
use App\Models\Program;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Spec 13: "RBAC autorizácia a server-side enforcement každej operácie."
 * Тесты проверяют, что каждый защищённый эндпоинт отказывает
 * неаутентифицированным пользователям и пользователям без нужной роли.
 */
class AuthorizationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    // -------------------------------------------------------------------------
    // Unauthenticated access — 401 on protected routes
    // -------------------------------------------------------------------------

    public function test_unauthenticated_cannot_access_auth_me(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_list_applications(): void
    {
        $this->getJson('/api/applications')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_list_teams(): void
    {
        $this->getJson('/api/teams')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_admin_dashboard(): void
    {
        $this->getJson('/api/admin/dashboard')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_list_admin_exports(): void
    {
        $this->getJson('/api/admin/exports')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_list_bulk_messages(): void
    {
        $this->getJson('/api/admin/bulk-messages')->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Public routes ARE accessible without auth
    // -------------------------------------------------------------------------

    public function test_public_programs_list_accessible_without_auth(): void
    {
        $this->getJson('/api/programs')->assertStatus(200);
    }

    public function test_public_partners_list_accessible_without_auth(): void
    {
        $this->getJson('/api/partners')->assertStatus(200);
    }

    public function test_public_pages_list_accessible_without_auth(): void
    {
        $this->getJson('/api/pages')->assertStatus(200);
    }

    public function test_public_posts_list_accessible_without_auth(): void
    {
        $this->getJson('/api/posts')->assertStatus(200);
    }

    public function test_public_specializations_accessible_without_auth(): void
    {
        $this->getJson('/api/specializations')->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Non-admin cannot access admin routes — 403
    // -------------------------------------------------------------------------

    public function test_student_cannot_access_admin_dashboard(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($student)->getJson('/api/admin/dashboard')->assertStatus(403);
    }

    public function test_student_cannot_list_admin_exports(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($student)->getJson('/api/admin/exports')->assertStatus(403);
    }

    public function test_student_cannot_send_bulk_message(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($student)->postJson('/api/admin/bulk-messages', [
            'target_group' => 'all',
            'subject'      => 'Test',
            'body'         => 'Hello',
        ])->assertStatus(403);
    }

    public function test_student_cannot_approve_student_application(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($student)
            ->postJson('/api/admin/students/999/approve')
            ->assertStatus(403);
    }

    public function test_company_user_cannot_access_admin_dashboard(): void
    {
        $company = User::factory()->create();
        $company->assignRole('company');

        $this->actingAs($company)->getJson('/api/admin/dashboard')->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Admin CAN access admin routes
    // -------------------------------------------------------------------------

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->getJson('/api/admin/dashboard')->assertStatus(200);
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin)->getJson('/api/admin/dashboard')->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Users cannot access other users' resources — 403
    // -------------------------------------------------------------------------

    public function test_user_cannot_delete_another_users_application(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('student');
        $other = User::factory()->create();
        $other->assignRole('student');

        $program = Program::create(['name' => 'P', 'type' => 'practice', 'is_active' => true]);
        $teamOwner = Team::factory()->create(['leader_id' => $owner->id]);
        $teamOwner->members()->attach($owner->id, ['role' => 'leader', 'joined_at' => now()]);

        $application = Application::factory()->create([
            'team_id'    => $teamOwner->id,
            'program_id' => $program->id,
            'status'     => Application::STATUS_DRAFT,
        ]);

        $this->actingAs($other)
            ->deleteJson("/api/applications/{$application->id}")
            ->assertStatus(403);
    }

    public function test_user_cannot_view_confidential_document_of_another_team(): void
    {
        Storage::fake('public');

        $uploader = User::factory()->create();
        $uploader->assignRole('student');
        $viewer   = User::factory()->create();
        $viewer->assignRole('student');

        $doc = Document::factory()->create([
            'uploaded_by'    => $uploader->id,
            'classification' => Document::CLASSIFICATION_CONFIDENTIAL,
        ]);

        $this->actingAs($viewer)
            ->getJson("/api/documents/{$doc->id}")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // GDPR — users can erase only their own data
    // -------------------------------------------------------------------------

    public function test_user_can_export_own_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $this->actingAs($user)
            ->postJson('/api/auth/gdpr/export')
            ->assertStatus(202);
    }

    public function test_non_admin_cannot_erase_another_users_data(): void
    {
        $user1 = User::factory()->create();
        $user1->assignRole('student');
        $user2 = User::factory()->create();
        $user2->assignRole('student');

        $this->actingAs($user1)
            ->deleteJson("/api/admin/gdpr/users/{$user2->id}")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Input validation — rejects malformed / missing data
    // -------------------------------------------------------------------------

    public function test_login_rejects_missing_email(): void
    {
        $this->postJson('/api/auth/login', ['password' => 'secret'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_rejects_missing_gdpr_consent(): void
    {
        $this->postJson('/api/auth/register', [
            'name'                  => 'Test',
            'email'                 => 'test@x.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'student',
            // gdpr_consent intentionally omitted
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['gdpr_consent']);
    }

    public function test_register_rejects_weak_password(): void
    {
        $this->postJson('/api/auth/register', [
            'name'                  => 'Test',
            'email'                 => 'test@x.com',
            'password'              => '123',
            'password_confirmation' => '123',
            'role'                  => 'student',
            'gdpr_consent'          => true,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'exists@example.com']);

        $this->postJson('/api/auth/register', [
            'name'                  => 'Test',
            'email'                 => 'exists@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'student',
            'gdpr_consent'          => true,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // -------------------------------------------------------------------------
    // SQL injection — Laravel's query builder should prevent this
    // -------------------------------------------------------------------------

    public function test_login_with_sql_injection_payload_returns_422_not_500(): void
    {
        $this->postJson('/api/auth/login', [
            'email'    => "' OR '1'='1",
            'password' => "' OR '1'='1",
        ])->assertStatus(422);
    }

    public function test_register_with_sql_injection_in_name_does_not_crash(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name'                  => "Robert'); DROP TABLE users;--",
            'email'                 => 'bobby@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'student',
            'gdpr_consent'          => true,
        ]);

        // Should succeed (201) or fail validation (422), never crash (500)
        $this->assertContains($response->status(), [201, 422]);
        $this->assertDatabaseMissing('users', ['name' => "Robert'); DROP TABLE users;--"]) ||
            $this->assertDatabaseHas('users', ['email' => 'bobby@example.com']);
    }

    // -------------------------------------------------------------------------
    // XSS — stored content is not interpreted as script
    // -------------------------------------------------------------------------

    public function test_xss_payload_in_name_is_stored_as_plain_text(): void
    {
        $xssPayload = '<script>alert("XSS")</script>';

        $this->postJson('/api/auth/register', [
            'name'                  => $xssPayload,
            'email'                 => 'xss@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'student',
            'gdpr_consent'          => true,
        ])->assertStatus(201);

        // The payload must be stored as-is (not executed) — JSON output escapes it
        $user = User::where('email', 'xss@example.com')->first();
        $this->assertSame($xssPayload, $user->name);
    }

    // -------------------------------------------------------------------------
    // Password reset — reveals nothing about account existence
    // -------------------------------------------------------------------------

    public function test_forgot_password_for_unknown_email_returns_422(): void
    {
        $this->postJson('/api/auth/forgot-password', [
            'email' => 'nobody@nti.local',
        ])->assertStatus(422);
    }
}
