<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_browse_the_public_partner_directory()
    {
        $organization = Organization::factory()->create(['name' => 'Acme Robotics']);

        $featured = Partner::factory()->create([
            'organization_id' => $organization->id,
            'is_featured'     => true,
        ]);
        $regular = Partner::factory()->create([
            'organization_id' => $organization->id,
            'is_featured'     => false,
        ]);

        $response = $this->getJson('/api/partners');

        $response->assertStatus(200);
        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($featured->id));
        $this->assertTrue($ids->contains($regular->id));

        // Featured partners are surfaced first for the public directory
        $this->assertEquals($featured->id, $response->json('0.id'));
    }

    public function test_anyone_can_view_a_single_partner_with_its_organization_reference()
    {
        $organization = Organization::factory()->create(['name' => 'Beta Systems']);
        $partner = Partner::factory()->create(['organization_id' => $organization->id]);

        $response = $this->getJson("/api/partners/{$partner->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $partner->id)
            ->assertJsonPath('organization.name', 'Beta Systems');
    }
}
