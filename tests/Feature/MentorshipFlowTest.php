<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Consultation;
use App\Models\Mentorship;
use App\Models\Milestone;
use App\Models\Program;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentorshipFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /** @return array{0: User, 1: Team, 2: Project} */
    private function createActiveProjectWithTeam(): array
    {
        $program = Program::create(['name' => 'NTI Practice', 'type' => 'practice', 'is_active' => true]);

        $leader = User::factory()->create();
        $leader->assignRole('student');

        $team = Team::create([
            'name'      => 'Team ' . $leader->name,
            'leader_id' => $leader->id,
            'status'    => 'active',
        ]);
        $team->members()->attach($leader->id, ['role' => 'leader', 'joined_at' => now()]);

        $application = Application::create([
            'program_id'   => $program->id,
            'team_id'      => $team->id,
            'status'       => Application::STATUS_ACTIVE,
            'submitted_at' => now(),
            'approved_at'  => now(),
        ]);

        $project = Project::create([
            'application_id' => $application->id,
            'title'          => 'Warehouse Robot MVP',
            'description'    => 'Build an MVP of the warehouse robot.',
            'status'         => 'active',
        ]);

        return [$leader, $team, $project];
    }

    public function test_admin_assigns_mentor_who_runs_a_consultation_and_approves_a_milestone()
    {
        [$leader, , $project] = $this->createActiveProjectWithTeam();

        $mentor = User::factory()->create();
        $mentor->assignRole('mentor');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // 1. Admin assigns the mentor to the project
        $assignResponse = $this->actingAs($admin)
            ->postJson('/api/mentorships', [
                'project_id' => $project->id,
                'mentor_id'  => $mentor->id,
                'status'     => 'active',
            ]);
        $assignResponse->assertStatus(201);

        $mentorship = Mentorship::findOrFail($assignResponse->json('id'));
        $this->assertEquals($project->id, $mentorship->project_id);
        $this->assertEquals($mentor->id, $mentorship->mentor_id);

        // 2. Admin schedules a milestone for the project
        $milestoneResponse = $this->actingAs($admin)
            ->postJson("/api/projects/{$project->id}/milestones", [
                'title'       => 'MVP demo',
                'description' => 'Show a working prototype to the mentor.',
                'deadline'    => now()->addWeeks(2)->toDateTimeString(),
            ]);
        $milestoneResponse->assertStatus(201);
        $milestone = Milestone::findOrFail($milestoneResponse->json('id'));

        // 3. Mentor schedules a consultation tied to the mentorship and milestone
        $consultationResponse = $this->actingAs($mentor)
            ->postJson('/api/consultations', [
                'mentorship_id' => $mentorship->id,
                'milestone_id'  => $milestone->id,
                'scheduled_at'  => now()->addDays(3)->toDateTimeString(),
            ]);
        $consultationResponse->assertStatus(201);

        $consultation = Consultation::findOrFail($consultationResponse->json('id'));
        $this->assertEquals($mentor->id, $consultation->mentor_id);

        // 4. The team leader (a project member, but neither the mentor nor a global viewer) can see the consultation
        $this->actingAs($leader)
            ->getJson("/api/consultations/{$consultation->id}")
            ->assertStatus(200)
            ->assertJsonPath('id', $consultation->id);

        // 5. A student outside the team has no access to the consultation
        $stranger = User::factory()->create();
        $stranger->assignRole('student');

        $this->actingAs($stranger)
            ->getJson("/api/consultations/{$consultation->id}")
            ->assertStatus(403);

        // 5b. The consultation list is scoped too: the stranger sees nothing, the team leader sees their own
        $this->actingAs($stranger)
            ->getJson('/api/consultations')
            ->assertStatus(200)
            ->assertExactJson([]);

        $this->actingAs($leader)
            ->getJson('/api/consultations')
            ->assertStatus(200)
            ->assertJsonPath('0.id', $consultation->id)
            ->assertJsonCount(1);

        // 6. The mentor (not a project team member) approves the milestone
        $this->actingAs($mentor)
            ->postJson("/api/milestones/{$milestone->id}/approve")
            ->assertStatus(200);

        $milestone->refresh();
        $this->assertTrue($milestone->isApproved());
        $this->assertEquals('completed', $milestone->status);
        $this->assertEquals($mentor->id, $milestone->approved_by);
    }
}
