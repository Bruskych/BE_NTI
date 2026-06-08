<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Evaluation;
use App\Models\EvaluationCriteria;
use App\Models\EvaluationTemplate;
use App\Models\Program;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_evaluator_scores_application_and_then_decides_its_fate()
    {
        $program = Program::create(['name' => 'NTI Grant Program', 'type' => 'grant', 'is_active' => true]);

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
            'status'       => Application::STATUS_IN_EVALUATION,
            'submitted_at' => now(),
        ]);

        $template = EvaluationTemplate::factory()->create(['program_id' => $program->id]);
        $criteriaA = EvaluationCriteria::factory()->create(['template_id' => $template->id, 'weight' => 0.6]);
        $criteriaB = EvaluationCriteria::factory()->create(['template_id' => $template->id, 'weight' => 0.4]);

        $evaluator = User::factory()->create();
        $evaluator->assignRole('evaluator');

        // 1. The evaluator scores the application against the weighted criteria
        $evaluationResponse = $this->actingAs($evaluator)
            ->postJson("/api/applications/{$application->id}/evaluations", [
                'recommendation' => 'approve',
                'comment'        => 'Strong technical proposal, well-prepared team.',
                'scores'         => [
                    ['criteria_id' => $criteriaA->id, 'score' => 9],
                    ['criteria_id' => $criteriaB->id, 'score' => 7],
                ],
            ]);
        $evaluationResponse->assertStatus(201);

        $evaluation = Evaluation::where('application_id', $application->id)->firstOrFail();
        $this->assertEquals($evaluator->id, $evaluation->evaluator_id);
        $this->assertEquals('approve', $evaluation->recommendation);
        $this->assertEqualsWithDelta(9 * 0.6 + 7 * 0.4, (float) $evaluation->total_score, 0.01);

        $this->assertDatabaseHas('audit_events', [
            'user_id'     => $evaluator->id,
            'action'      => 'evaluation_submitted',
            'object_type' => 'evaluation',
            'object_id'   => $evaluation->id,
            'result'      => 'success',
        ]);

        // 2. A student (not an evaluator) cannot score the application
        $this->actingAs($leader)
            ->postJson("/api/applications/{$application->id}/evaluations", [
                'recommendation' => 'approve',
                'scores'         => [['criteria_id' => $criteriaA->id, 'score' => 5]],
            ])
            ->assertStatus(403);

        // 3. The same evaluator cannot score the same application twice
        $this->actingAs($evaluator)
            ->postJson("/api/applications/{$application->id}/evaluations", [
                'recommendation' => 'reject',
                'scores'         => [['criteria_id' => $criteriaA->id, 'score' => 4]],
            ])
            ->assertStatus(403);

        // 4. The evaluator makes the final decision based on the evaluation
        $decisionResponse = $this->actingAs($evaluator)
            ->postJson("/api/applications/{$application->id}/decide", [
                'decision' => 'approve',
                'comment'  => 'Approved by the evaluation committee.',
            ]);

        $decisionResponse->assertStatus(200)
            ->assertJsonPath('status', Application::STATUS_APPROVED)
            ->assertJsonPath('decision_comment', 'Approved by the evaluation committee.');

        $application->refresh();
        $this->assertTrue($application->isApproved());
        $this->assertNotNull($application->approved_at);
        $this->assertEqualsWithDelta((float) $evaluation->total_score, (float) $application->total_score, 0.01);

        $this->assertDatabaseHas('application_history', [
            'application_id' => $application->id,
            'old_status'     => Application::STATUS_IN_EVALUATION,
            'new_status'     => Application::STATUS_APPROVED,
            'changed_by'     => $evaluator->id,
        ]);

        $this->assertDatabaseHas('audit_events', [
            'user_id'     => $evaluator->id,
            'action'      => 'application_decided',
            'object_type' => 'application',
            'object_id'   => $application->id,
            'result'      => 'success',
        ]);

        // 5. A decided application is no longer awaiting a decision
        $this->actingAs($evaluator)
            ->postJson("/api/applications/{$application->id}/decide", ['decision' => 'reject'])
            ->assertStatus(403);
    }
}
