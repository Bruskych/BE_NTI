<?php

namespace Tests\Feature;

use App\Models\Call;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function callWithStatus(string $status): Call
    {
        $program = Program::create(['name' => 'NTI Program', 'type' => 'practice', 'is_active' => true]);

        return Call::factory()->create([
            'program_id' => $program->id,
            'status'     => $status,
        ]);
    }

    public function test_admin_can_view_update_and_delete_a_draft_call()
    {
        $call = $this->callWithStatus('draft');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->getJson("/api/calls/{$call->id}")->assertOk();

        $this->actingAs($admin)
            ->putJson("/api/calls/{$call->id}", ['title' => 'Updated title'])
            ->assertOk()
            ->assertJsonPath('title', 'Updated title');

        $this->actingAs($admin)->deleteJson("/api/calls/{$call->id}")->assertOk();
        $this->assertSoftDeleted($call);
    }

    public function test_admin_can_open_and_close_a_call()
    {
        $call = $this->callWithStatus('draft');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->postJson("/api/calls/{$call->id}/open")->assertOk();
        $this->assertSame('open', $call->fresh()->status);

        $this->actingAs($admin)->postJson("/api/calls/{$call->id}/close")->assertOk();
        $this->assertSame('closed', $call->fresh()->status);
    }

    public function test_user_without_calls_permissions_cannot_view_a_draft_call_but_can_view_an_open_call()
    {
        $draftCall = $this->callWithStatus('draft');
        $openCall = $this->callWithStatus('open');

        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($student)->getJson("/api/calls/{$draftCall->id}")->assertForbidden();
        $this->actingAs($student)->getJson("/api/calls/{$openCall->id}")->assertOk();
    }

    public function test_user_without_calls_permissions_cannot_update_open_close_or_delete_a_call()
    {
        $call = $this->callWithStatus('draft');

        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($student)->putJson("/api/calls/{$call->id}", ['title' => 'Hacked'])->assertForbidden();
        $this->actingAs($student)->postJson("/api/calls/{$call->id}/open")->assertForbidden();
        $this->actingAs($student)->postJson("/api/calls/{$call->id}/close")->assertForbidden();
        $this->actingAs($student)->deleteJson("/api/calls/{$call->id}")->assertForbidden();
    }
}
