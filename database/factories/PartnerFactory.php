<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Organization;
use App\Models\Partner;

/**
 * @extends Factory<Partner>
 */
class PartnerFactory extends Factory
{
    /**
     * Партнёры системы
     *
     * @return array<string, mixed>
     */
    protected $model = Partner::class;
    public function definition(): array
    {
        return [
            'organization_id'   => fn() => Organization::inRandomOrder()->first()?->id ?? Organization::factory(),
            'logo_path'         => 'partners/logos/' . fake()->uuid() . '.png',
            'website_link'      => fake()->url(),
            'is_featured'       => fake()->boolean(30),
        ];
    }
}
