<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Organization;
use App\Models\User;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Фабрика для генерации компаний/фирм
     *
     * @return array<string, mixed>
     */
    protected $model = Organization::class;
    public function definition(): array {
        return [
            'name'          => fake()->company().' s.r.o.',
            'tax_id'        => 'SK'.fake()->unique()->numberBetween(1000000000, 2999999999),
            'sector'        => fake()->randomElement(
                [
                    'IT & Technology',
                    'Marketing & Advertising',
                    'Finance',
                    'Logistics',
                    'Education',
                    'Energy'
                ]
            ),
            'website_link'  => fake()->url(),
            'description'   => fake()->realText(300),
            'status'        => 'active',
        ];
    }

    public function inactive(): static {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function withOwner(User $user): static {
        return $this->afterCreating(fn (
            Organization $organization) => $organization->users()->attach($user->id, ['role' => 'owner']
        ));
    }

    public function withProductOwner(User $user): static {
        return $this->afterCreating(fn (
            Organization $organization) => $organization->users()->attach($user->id, ['role' => 'product_owner']
        ));
    }

    public function withMember(User $user): static {
        return $this->afterCreating(fn (
            Organization $organization) => $organization->users()->attach($user->id, ['role' => 'member']
        ));
    }
}
