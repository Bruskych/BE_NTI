<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Evaluation;
use App\Models\EvaluationCriteria;
use App\Models\Organization;
use App\Models\Program;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected Program $program;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'student', 'company', 'evaluator'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->program = Program::create([
            'name' => 'Test Program',
            'type' => 'practice',
            'is_active' => true,
        ]);
    }

    public function test_approving_student_application_logs_audit_events_and_assigns_role()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $leader = User::factory()->create();
        $team = Team::factory()->create(['leader_id' => $leader->id]);
        $application = Application::factory()->create([
            'program_id' => $this->program->id,
            'team_id' => $team->id,
            'organization_id' => null,
            'status' => Application::STATUS_SUBMITTED,
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/api/admin/students/{$application->id}/approve", ['comment' => 'Looks great']);

        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_events', [
            'user_id' => $admin->id,
            'action' => 'student_application_approved',
            'object_type' => 'application',
            'object_id' => $application->id,
            'result' => 'success',
        ]);

        $this->assertDatabaseHas('audit_events', [
            'user_id' => $admin->id,
            'action' => 'role_changed',
            'object_type' => 'user',
            'object_id' => $leader->id,
            'result' => 'success',
        ]);

        $this->assertTrue($leader->fresh()->hasRole('student'));
    }

    public function test_rejecting_company_application_logs_audit_event()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $leader = User::factory()->create();
        $organization = Organization::factory()->create();
        $team = Team::factory()->create(['leader_id' => $leader->id]);
        $application = Application::factory()->create([
            'program_id' => $this->program->id,
            'team_id' => $team->id,
            'organization_id' => $organization->id,
            'status' => Application::STATUS_SUBMITTED,
        ]);

        $response = $this->actingAs($admin)
            ->postJson("/api/admin/companies/{$application->id}/reject", ['comment' => 'Missing documents']);

        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_events', [
            'user_id' => $admin->id,
            'action' => 'company_application_rejected',
            'object_type' => 'application',
            'object_id' => $application->id,
            'result' => 'success',
        ]);
    }

    public function test_submitting_evaluation_logs_audit_event()
    {
        $expert = User::factory()->create();
        $expert->assignRole('evaluator');

        $team = Team::factory()->create();
        $application = Application::factory()->create([
            'program_id' => $this->program->id,
            'team_id' => $team->id,
            'status' => Application::STATUS_IN_EVALUATION,
        ]);
        $criteria = EvaluationCriteria::factory()->create(['weight' => 1]);

        $response = $this->actingAs($expert)
            ->postJson("/api/applications/{$application->id}/evaluations", [
                'recommendation' => 'approve',
                'comment' => 'Solid work',
                'scores' => [
                    ['criteria_id' => $criteria->id, 'score' => 8],
                ],
            ]);

        $response->assertStatus(201);

        $evaluation = Evaluation::where('application_id', $application->id)->firstOrFail();

        $this->assertDatabaseHas('audit_events', [
            'user_id' => $expert->id,
            'action' => 'evaluation_submitted',
            'object_type' => 'evaluation',
            'object_id' => $evaluation->id,
            'result' => 'success',
        ]);
    }
}
