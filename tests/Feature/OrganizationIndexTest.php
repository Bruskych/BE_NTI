<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_admin_sees_every_organization_regardless_of_status()
    {
        $active = Organization::factory()->create(['status' => 'active']);
        $inactive = Organization::factory()->create(['status' => 'inactive']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->getJson('/api/organizations');

        $response->assertOk();
        $ids = collect($response->json())->pluck('id')->all();
        $this->assertContains($active->id, $ids);
        $this->assertContains($inactive->id, $ids);
    }

    public function test_member_sees_active_organizations_plus_their_own_inactive_organization()
    {
        $active = Organization::factory()->create(['status' => 'active']);
        $ownInactive = Organization::factory()->create(['status' => 'inactive']);
        $foreignInactive = Organization::factory()->create(['status' => 'inactive']);

        // A "student" role has no organizations.view permission, so visibility
        // is governed purely by the active/own-membership scoping in the index query.
        $member = User::factory()->create();
        $member->assignRole('student');
        $ownInactive->users()->attach($member->id, ['role' => 'owner']);

        $response = $this->actingAs($member)->getJson('/api/organizations');

        $response->assertOk();
        $ids = collect($response->json())->pluck('id')->all();
        $this->assertContains($active->id, $ids);
        $this->assertContains($ownInactive->id, $ids);
        $this->assertNotContains($foreignInactive->id, $ids);
    }

    public function test_stranger_sees_only_active_organizations()
    {
        $active = Organization::factory()->create(['status' => 'active']);
        Organization::factory()->create(['status' => 'inactive']);

        $stranger = User::factory()->create();
        $stranger->assignRole('student');

        $response = $this->actingAs($stranger)->getJson('/api/organizations');

        $response->assertOk();
        $ids = collect($response->json())->pluck('id')->all();
        $this->assertSame([$active->id], $ids);
    }
}
