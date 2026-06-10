<?php

namespace Tests\Unit;

use App\Models\Application;
use App\Models\Call;
use App\Models\Program;
use App\Models\Team;
use App\Models\User;
use App\Services\ApplicationService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ApplicationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->service = app(ApplicationService::class);
    }

    public function test_createApplication_sets_draft_status(): void
    {
        $program = Program::create(['name' => 'P', 'type' => 'practice', 'is_active' => true]);
        $leader  = User::factory()->create();
        $team    = Team::factory()->create(['leader_id' => $leader->id]);

        $application = $this->service->createApplication(
            ['program_id' => $program->id],
            $team->id,
            $leader->id
        );

        $this->assertSame(Application::STATUS_DRAFT, $application->status);
        $this->assertSame($team->id, $application->team_id);
    }

    public function test_createApplication_records_history_entry(): void
    {
        $program = Program::create(['name' => 'P', 'type' => 'practice', 'is_active' => true]);
        $leader  = User::factory()->create();
        $team    = Team::factory()->create(['leader_id' => $leader->id]);

        $application = $this->service->createApplication(
            ['program_id' => $program->id],
            $team->id,
            $leader->id
        );

        $this->assertDatabaseHas('application_history', [
            'application_id' => $application->id,
            'new_status'     => Application::STATUS_DRAFT,
        ]);
    }

    public function test_submitApplication_changes_status_to_submitted(): void
    {
        $program = Program::create(['name' => 'P', 'type' => 'practice', 'is_active' => true]);
        $call    = Call::factory()->create(['program_id' => $program->id, 'status' => 'open']);
        $leader  = User::factory()->create();
        $team    = Team::factory()->create(['leader_id' => $leader->id]);
        $team->members()->attach($leader->id, ['role' => 'leader', 'joined_at' => now()]);
        $team->members()->attach(User::factory()->create()->id, ['role' => 'member', 'joined_at' => now()]);
        $team->members()->attach(User::factory()->create()->id, ['role' => 'member', 'joined_at' => now()]);

        $application = Application::create([
            'program_id' => $program->id,
            'call_id'    => $call->id,
            'team_id'    => $team->id,
            'status'     => Application::STATUS_DRAFT,
        ]);

        $this->service->submitApplication($application, $leader->id);
        $application->refresh();

        $this->assertSame(Application::STATUS_SUBMITTED, $application->status);
        $this->assertNotNull($application->submitted_at);
    }

    public function test_submitApplication_fails_if_not_draft(): void
    {
        $this->expectException(ValidationException::class);

        $program = Program::create(['name' => 'P', 'type' => 'practice', 'is_active' => true]);
        $leader  = User::factory()->create();
        $team    = Team::factory()->create(['leader_id' => $leader->id]);

        $application = Application::create([
            'program_id' => $program->id,
            'team_id'    => $team->id,
            'status'     => Application::STATUS_SUBMITTED,
        ]);

        $this->service->submitApplication($application, $leader->id);
    }

    public function test_decideApplication_sets_approved_status(): void
    {
        $program = Program::create(['name' => 'P', 'type' => 'practice', 'is_active' => true]);
        $leader  = User::factory()->create();
        $team    = Team::factory()->create(['leader_id' => $leader->id]);
        $admin   = User::factory()->create();

        $application = Application::create([
            'program_id' => $program->id,
            'team_id'    => $team->id,
            'status'     => Application::STATUS_SUBMITTED,
        ]);

        $result = $this->service->decideApplication(
            $application,
            'approve',
            'Looks good',
            $admin->id
        );

        $this->assertSame(Application::STATUS_APPROVED, $result->status);
    }

    public function test_decideApplication_sets_rejected_status(): void
    {
        $program = Program::create(['name' => 'P', 'type' => 'practice', 'is_active' => true]);
        $leader  = User::factory()->create();
        $team    = Team::factory()->create(['leader_id' => $leader->id]);
        $admin   = User::factory()->create();

        $application = Application::create([
            'program_id' => $program->id,
            'team_id'    => $team->id,
            'status'     => Application::STATUS_SUBMITTED,
        ]);

        $result = $this->service->decideApplication(
            $application,
            'reject',
            'Not qualified',
            $admin->id
        );

        $this->assertSame(Application::STATUS_REJECTED, $result->status);
    }

    public function test_decideApplication_sets_needs_supplement_status(): void
    {
        $program = Program::create(['name' => 'P', 'type' => 'practice', 'is_active' => true]);
        $leader  = User::factory()->create();
        $team    = Team::factory()->create(['leader_id' => $leader->id]);
        $admin   = User::factory()->create();

        $application = Application::create([
            'program_id' => $program->id,
            'team_id'    => $team->id,
            'status'     => Application::STATUS_SUBMITTED,
        ]);

        $result = $this->service->decideApplication(
            $application,
            'request_supplement',
            'Missing documents',
            $admin->id
        );

        $this->assertSame(Application::STATUS_NEEDS_SUPPLEMENT, $result->status);
    }
}
