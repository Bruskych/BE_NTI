<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\GdprConsent;
use App\Models\User;

/**
 * @extends Factory<GdprConsent>
 */
class GdprConsentFactory extends Factory
{
    /**
     * Структура согласий GDPR
     *
     * @return array<string, mixed>
     */
    protected $model = GdprConsent::class;
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-3 months', 'now');
        return [
            'user_id'       => fn() => User::inRandomOrder()->first()?->id ?? User::factory(),
            'consent_type'  => fake()->randomElement(
                [
                    'privacy_policy',
                    'terms_of_service',
                    'cookie_policy'
                ]
            ),
            'version'       => fake()->randomElement(
                [
                    '1.0',
                    '1.1',
                    '2.0'
                ]
            ),
            'accepted_at'   => $date,
            'ip_address'    => fake()->ipv4(),
            'created_at'    => $date,
        ];
    }
}
