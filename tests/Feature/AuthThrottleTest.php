<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('auth:127.0.0.1');
    }

    public function test_login_attempts_are_rate_limited_per_ip()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', [
                'email'    => 'nobody@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(422);
        }

        // Spec 13: "Rate limiting na prihlasovanie..." — the 6th attempt within the window is throttled
        $this->postJson('/api/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_registration_attempts_are_rate_limited_per_ip()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/register', [
                'name'                  => "User {$i}",
                'email'                 => "user{$i}@example.com",
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'gdpr_consent'          => true,
                'role'                  => 'student',
            ]);
        }

        $this->postJson('/api/auth/register', [
            'name'                  => 'User 6',
            'email'                 => 'user6@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'gdpr_consent'          => true,
            'role'                  => 'student',
        ])->assertStatus(429);
    }
}
