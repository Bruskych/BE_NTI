<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\User;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Фабрика для автогенерации пользователей
     *
     * @return array<string, mixed>
     */
    protected $model = User::class;
    protected static ?string $password;
    public function definition(): array {
        return [
            'name'                  => fake()->name(),
            'email'                 => fake()->unique()->safeEmail(),
            'avatar_path'           => null,
            'email_verified_at'     => now(),
            'password'              => static::$password ??= Hash::make('password'),
            'remember_token'        => Str::random(10),
        ];
    }

    public function unverified(): static {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withAvatar(): static {
        return $this->state(fn (array $attributes) => [
            'avatar_path' => 'avatars/' . fake()->uuid() . '.jpg',
        ]);
    }

    public function superAdmin(): static {
        return $this->afterCreating(fn (User $user) => $user->assignRole('super_admin'));
    }

    public function admin(): static {
        return $this->afterCreating(fn (User $user) => $user->assignRole('admin'));
    }

    public function contentEditor(): static {
        return $this->afterCreating(fn (User $user) => $user->assignRole('content_editor'));
    }

    public function evaluator(): static {
        return $this->afterCreating(fn (User $user) => $user->assignRole('evaluator'));
    }

    public function mentor(): static {
        return $this->afterCreating(fn (User $user) => $user->assignRole('mentor'));
    }

    public function teamLeader(): static {
        return $this->afterCreating(fn (User $user) => $user->assignRole('team_leader'));
    }

    public function company(): static {
        return $this->afterCreating(fn (User $user) => $user->assignRole('company'));
    }

    public function student(): static {
        return $this->afterCreating(fn (User $user) => $user->assignRole('student'));
    }

    public function visitor(): static {
        return $this->afterCreating(fn (User $user) => $user->assignRole('visitor'));
    }
}
