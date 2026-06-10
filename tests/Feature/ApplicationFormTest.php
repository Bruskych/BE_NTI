<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationPairingSubmission;
use App\Models\Call;
use App\Models\Challenge;
use App\Models\FormField;
use App\Models\Organization;
use App\Models\Program;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function createLeader(): User
    {
        $leader = User::factory()->create();
        $leader->assignRole('student');

        $team = Team::create([
            'name'      => 'Team ' . $leader->name,
            'leader_id' => $leader->id,
            'status'    => 'active',
        ]);
        $team->members()->attach($leader->id, ['role' => 'leader', 'joined_at' => now()]);

        return $leader;
    }

    public function test_program_form_fields_are_publicly_visible_and_filtered_by_call()
    {
        $program = Program::create(['name' => 'NTI Grant Program', 'type' => 'grant', 'is_active' => true]);
        $call = Call::factory()->create(['program_id' => $program->id, 'status' => 'open']);

        $programWide = FormField::create([
            'program_id' => $program->id,
            'call_id'    => null,
            'name'       => 'motivation_letter',
            'label'      => 'Motivation Letter',
            'type'       => 'textarea',
            'required'   => true,
            'order'      => 1,
        ]);

        $callSpecific = FormField::create([
            'program_id' => $program->id,
            'call_id'    => $call->id,
            'name'       => 'github_repository',
            'label'      => 'GitHub Link',
            'type'       => 'text',
            'required'   => true,
            'order'      => 2,
        ]);

        $otherCall = Call::factory()->create(['program_id' => $program->id, 'status' => 'open']);
        FormField::create([
            'program_id' => $program->id,
            'call_id'    => $otherCall->id,
            'name'       => 'other_call_field',
            'label'      => 'Should not appear',
            'type'       => 'text',
            'required'   => false,
            'order'      => 3,
        ]);

        $response = $this->getJson("/api/programs/{$program->id}/form-fields?call_id={$call->id}");

        $response->assertStatus(200);
        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($programWide->id));
        $this->assertTrue($ids->contains($callSpecific->id));
        $this->assertCount(2, $ids);
    }

    public function test_student_must_fill_required_fields_before_submitting_program_a_application()
    {
        Storage::fake('public');

        $program = Program::create(['name' => 'NTI Grant Program', 'type' => 'grant', 'is_active' => true]);
        $call = Call::factory()->create(['program_id' => $program->id, 'status' => 'open']);

        $requiredField = FormField::create([
            'program_id' => $program->id,
            'call_id'    => null,
            'name'       => 'motivation_letter',
            'label'      => 'Motivation Letter',
            'type'       => 'textarea',
            'required'   => true,
            'order'      => 1,
        ]);

        $optionalField = FormField::create([
            'program_id' => $program->id,
            'call_id'    => null,
            'name'       => 'portfolio_link',
            'label'      => 'Portfolio Link',
            'type'       => 'text',
            'required'   => false,
            'order'      => 2,
        ]);

        $leader = $this->createLeader();
        $team = $leader->teams()->where('teams.leader_id', $leader->id)->firstOrFail();
        $team->members()->attach(User::factory()->create()->id, ['role' => 'member', 'joined_at' => now()]);
        $team->members()->attach(User::factory()->create()->id, ['role' => 'member', 'joined_at' => now()]);

        $createResponse = $this->actingAs($leader)
            ->postJson('/api/applications', ['program_id' => $program->id, 'call_id' => $call->id]);
        $createResponse->assertStatus(201);
        $application = Application::findOrFail($createResponse->json('id'));

        // 1. Submitting without filling the required field is rejected
        $this->actingAs($leader)
            ->postJson("/api/applications/{$application->id}/submit")
            ->assertStatus(422)
            ->assertJsonValidationErrors(["answers.{$requiredField->id}"]);

        // 2. The leader saves answers progressively (draft autosave)
        $updateResponse = $this->actingAs($leader)
            ->putJson("/api/applications/{$application->id}", [
                'answers' => [
                    ['field_id' => $requiredField->id, 'value_text' => 'I really want to join this program.'],
                    ['field_id' => $optionalField->id, 'value_text' => 'github.com/ivan'],
                ],
            ]);
        $updateResponse->assertStatus(200);

        $this->assertDatabaseHas('application_answers', [
            'application_id' => $application->id,
            'field_id'       => $requiredField->id,
            'value_text'     => 'I really want to join this program.',
        ]);

        // 3. Now submission succeeds
        $this->actingAs($leader)
            ->postJson("/api/applications/{$application->id}/submit")
            ->assertStatus(200);

        $this->assertEquals(Application::STATUS_SUBMITTED, $application->fresh()->status);
    }

    public function test_program_b_application_requires_pairing_submissions_before_submit()
    {
        Storage::fake('public');

        $program = Program::create(['name' => 'NTI Practice', 'type' => 'practice', 'is_active' => true]);
        $organization = Organization::factory()->create();
        $challenge = Challenge::factory()->create([
            'program_id'      => $program->id,
            'organization_id' => $organization->id,
            'status'          => 'published',
        ]);

        $leader = $this->createLeader();

        $createResponse = $this->actingAs($leader)
            ->postJson('/api/applications', ['program_id' => $program->id, 'challenge_id' => $challenge->id]);
        $createResponse->assertStatus(201);
        $application = Application::findOrFail($createResponse->json('id'));
        $this->assertTrue($application->isProgramB());

        // 1. Submission is rejected until CV, motivation letter and solution proposal are attached
        $this->actingAs($leader)
            ->postJson("/api/applications/{$application->id}/submit")
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'pairing_submissions.cv',
                'pairing_submissions.motivation_letter',
                'pairing_submissions.solution_proposal',
            ]);

        // 2. The leader uploads the three required selection documents
        $this->actingAs($leader)
            ->putJson("/api/applications/{$application->id}", [
                'pairing_submissions' => [
                    ['type' => 'cv', 'file' => UploadedFile::fake()->create('cv.pdf', 50)],
                    ['type' => 'motivation_letter', 'file' => UploadedFile::fake()->create('motivation.pdf', 50)],
                    ['type' => 'solution_proposal', 'file' => UploadedFile::fake()->create('proposal.pdf', 50)],
                ],
            ])
            ->assertStatus(200);

        $this->assertSame(3, ApplicationPairingSubmission::where('application_id', $application->id)->count());
        $this->assertDatabaseHas('application_pairing_submissions', [
            'application_id' => $application->id,
            'type'           => ApplicationPairingSubmission::TYPE_CV,
        ]);

        // 3. Submission now succeeds
        $this->actingAs($leader)
            ->postJson("/api/applications/{$application->id}/submit")
            ->assertStatus(200);

        $this->assertEquals(Application::STATUS_SUBMITTED, $application->fresh()->status);
    }
}
