<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Challenge;
use App\Models\Program;
use App\Models\Specialization;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyChallengeFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_company_can_register_get_approved_and_publish_a_challenge()
    {
        $program = Program::create(['name' => 'NTI Internships', 'type' => 'grant', 'is_active' => true]);
        $specialization = Specialization::create(['name' => 'Backend Development', 'slug' => 'backend-development']);

        // 1. Company representative registers an account
        $this->postJson('/api/auth/register', [
            'name'                  => 'Maria Founder',
            'email'                 => 'maria@acme.example',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'gdpr_consent'          => true,
            'role'                  => 'company',
            'company_name'          => 'Acme Robotics',
            'company_tax_id'        => '12345678',
            'sector'                => 'Robotics',
            'website_link'          => 'https://acme.example',
            'description'           => 'We build robots.',
        ])->assertStatus(201);

        $owner = User::where('email', 'maria@acme.example')->firstOrFail();
        $this->assertTrue($owner->hasRole('visitor'));

        $organization = $owner->organizations()->firstOrFail();
        $this->assertEquals('inactive', $organization->status);
        $this->assertTrue($owner->isOwnerOf($organization));

        $registrationApplication = Application::where('organization_id', $organization->id)->firstOrFail();
        $this->assertEquals(Application::STATUS_SUBMITTED, $registrationApplication->status);

        // 2. Admin reviews and approves the company registration -> "company" role granted, organization activated
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->postJson("/api/admin/companies/{$registrationApplication->id}/approve", ['comment' => 'Looks legit'])
            ->assertStatus(200);

        $owner = $owner->fresh();
        $organization = $organization->fresh();
        $this->assertTrue($owner->hasRole('company'));
        $this->assertEquals('active', $organization->status);

        // 3. Company creates a draft challenge for its own organization
        $createResponse = $this->actingAs($owner)
            ->postJson('/api/challenges', [
                'program_id'         => $program->id,
                'title'              => 'Build a warehouse robot',
                'description'        => 'Design and prototype an autonomous warehouse robot.',
                'deadline'           => now()->addMonth()->toDateTimeString(),
                'status'             => 'draft',
                'max_applications'   => 5,
                'specialization_ids' => [$specialization->id],
            ]);
        $createResponse->assertStatus(201);

        $challenge = Challenge::findOrFail($createResponse->json('id'));
        $this->assertEquals($organization->id, $challenge->organization_id);
        $this->assertEquals($owner->id, $challenge->product_owner_id);
        $this->assertTrue($challenge->isDraft());

        // 4. Company publishes its own challenge
        $this->actingAs($owner)
            ->putJson("/api/challenges/{$challenge->id}", ['status' => 'published'])
            ->assertStatus(200)
            ->assertJsonPath('status', 'published');

        $this->assertTrue($challenge->fresh()->isPublished());

        // 5. A representative of an unrelated company cannot edit or delete someone else's challenge
        $stranger = User::factory()->create();
        $stranger->assignRole('company');

        $this->actingAs($stranger)
            ->putJson("/api/challenges/{$challenge->id}", ['status' => 'closed'])
            ->assertStatus(403);

        $this->actingAs($stranger)
            ->deleteJson("/api/challenges/{$challenge->id}")
            ->assertStatus(403);

        $this->assertEquals('published', $challenge->fresh()->status);
    }

    public function test_unapproved_company_cannot_create_a_challenge()
    {
        $program = Program::create(['name' => 'NTI Internships', 'type' => 'grant', 'is_active' => true]);
        $specialization = Specialization::create(['name' => 'Data Science', 'slug' => 'data-science']);

        $this->postJson('/api/auth/register', [
            'name'                  => 'Petro Founder',
            'email'                 => 'petro@beta.example',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'gdpr_consent'          => true,
            'role'                  => 'company',
            'company_name'          => 'Beta Systems',
            'company_tax_id'        => '87654321',
            'sector'                => 'Software',
            'website_link'          => 'https://beta.example',
            'description'           => 'We build software.',
        ])->assertStatus(201);

        $owner = User::where('email', 'petro@beta.example')->firstOrFail();
        $this->assertTrue($owner->hasRole('visitor'));
        $this->assertFalse($owner->hasRole('company'));

        $this->actingAs($owner)
            ->postJson('/api/challenges', [
                'program_id'         => $program->id,
                'title'              => 'Should not be created',
                'description'        => 'N/A',
                'deadline'           => now()->addMonth()->toDateTimeString(),
                'status'             => 'draft',
                'max_applications'   => 5,
                'specialization_ids' => [$specialization->id],
            ])
            ->assertStatus(403);

        $this->assertDatabaseMissing('challenges', ['title' => 'Should not be created']);
    }
}
