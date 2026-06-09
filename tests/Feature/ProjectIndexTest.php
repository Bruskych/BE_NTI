<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Program;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function projectFor(Program $program, User $leader): Project
    {
        $team = Team::create([
            'name'      => 'Team ' . $leader->name,
            'leader_id' => $leader->id,
            'status'    => 'active',
        ]);
        $team->members()->attach($leader->id, ['role' => 'leader', 'joined_at' => now()]);

        $application = Application::create([
            'program_id'   => $program->id,
            'team_id'      => $team->id,
            'status'       => Application::STATUS_APPROVED,
            'submitted_at' => now(),
        ]);

        return Project::factory()->create(['application_id' => $application->id]);
    }

    public function test_admin_sees_all_projects()
    {
        $program = Program::create(['name' => 'NTI Program', 'type' => 'practice', 'is_active' => true]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $project1 = $this->projectFor($program, tap(User::factory()->create())->assignRole('student'));
        $project2 = $this->projectFor($program, tap(User::factory()->create())->assignRole('student'));

        $response = $this->actingAs($admin)->getJson('/api/projects');

        $response->assertOk();
        $ids = collect($response->json())->pluck('id')->all();
        $this->assertContains($project1->id, $ids);
        $this->assertContains($project2->id, $ids);
    }

    public function test_team_member_sees_only_their_own_project()
    {
        $program = Program::create(['name' => 'NTI Program', 'type' => 'practice', 'is_active' => true]);

        $leader = User::factory()->create();
        $leader->assignRole('student');
        $ownProject = $this->projectFor($program, $leader);

        $stranger = User::factory()->create();
        $stranger->assignRole('student');
        $this->projectFor($program, $stranger);

        $response = $this->actingAs($leader)->getJson('/api/projects');

        $response->assertOk();
        $ids = collect($response->json())->pluck('id')->all();
        $this->assertSame([$ownProject->id], $ids);
    }

    public function test_user_with_no_related_projects_sees_an_empty_list()
    {
        $program = Program::create(['name' => 'NTI Program', 'type' => 'practice', 'is_active' => true]);

        $owner = User::factory()->create();
        $owner->assignRole('student');
        $this->projectFor($program, $owner);

        $stranger = User::factory()->create();
        $stranger->assignRole('student');

        $response = $this->actingAs($stranger)->getJson('/api/projects');

        $response->assertOk();
        $this->assertSame([], $response->json());
    }
}
