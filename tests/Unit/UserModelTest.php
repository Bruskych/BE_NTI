<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserWithRole(string $role): User
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    public function test_isStudent_returns_true_for_student_role(): void
    {
        $user = $this->makeUserWithRole('student');
        $this->assertTrue($user->isStudent());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isCompany());
    }

    public function test_isAdmin_returns_true_for_admin_role(): void
    {
        $user = $this->makeUserWithRole('admin');
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isStudent());
    }

    public function test_isAdmin_returns_true_for_super_admin_role(): void
    {
        $user = $this->makeUserWithRole('super_admin');
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_isCompany_returns_true_for_company_role(): void
    {
        $user = $this->makeUserWithRole('company');
        $this->assertTrue($user->isCompany());
        $this->assertFalse($user->isStudent());
    }

    public function test_isMentor_returns_true_for_mentor_role(): void
    {
        $user = $this->makeUserWithRole('mentor');
        $this->assertTrue($user->isMentor());
    }

    public function test_isTeamLeader_returns_true_for_team_leader_role(): void
    {
        $user = $this->makeUserWithRole('team_leader');
        $this->assertTrue($user->isTeamLeader());
    }

    public function test_isStaff_returns_true_for_admin(): void
    {
        $user = $this->makeUserWithRole('admin');
        $this->assertTrue($user->isStaff());
    }

    public function test_isStaff_returns_true_for_evaluator(): void
    {
        $user = $this->makeUserWithRole('evaluator');
        $this->assertTrue($user->isStaff());
    }

    public function test_isStaff_returns_false_for_student(): void
    {
        $user = $this->makeUserWithRole('student');
        $this->assertFalse($user->isStaff());
    }

    public function test_isManagement_returns_true_for_admin(): void
    {
        $user = $this->makeUserWithRole('admin');
        $this->assertTrue($user->isManagement());
    }

    public function test_isManagement_returns_false_for_mentor(): void
    {
        $user = $this->makeUserWithRole('mentor');
        $this->assertFalse($user->isManagement());
    }

    public function test_avatar_url_is_null_when_no_avatar(): void
    {
        $user = User::factory()->create(['avatar_path' => null]);
        $this->assertNull($user->avatar_url);
    }

    public function test_isOwnerOf_returns_true_for_owner(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org->id, ['role' => 'owner']);

        $this->assertTrue($user->isOwnerOf($org));
    }

    public function test_isOwnerOf_returns_false_for_non_owner_member(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org->id, ['role' => 'member']);

        $this->assertFalse($user->isOwnerOf($org));
    }

    public function test_belongsToOrg_returns_true_for_any_role(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org->id, ['role' => 'member']);

        $this->assertTrue($user->belongsToOrg($org));
    }

    public function test_belongsToOrg_returns_false_when_not_attached(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();

        $this->assertFalse($user->belongsToOrg($org));
    }

    public function test_user_hidden_fields_are_not_serialized(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }
}
