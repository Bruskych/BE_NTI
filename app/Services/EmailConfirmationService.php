<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class EmailConfirmationService
{
    const REDIS_PREFIX = 'email_confirmation:';
    const DEFAULT_EXPIRES_IN = 3600; // 1 hour

    /**
     * Generate confirmation code and store in Redis
     */
    public function generateCode(string $email, array $data = []): string
    {
        $code = Str::random(6);
        $key = self::REDIS_PREFIX . $email;

        Redis::setex(
            $key,
            self::DEFAULT_EXPIRES_IN,
            json_encode([
                'code' => $code,
                'data' => $data,
                'created_at' => now()->toIso8601String(),
            ])
        );

        return $code;
    }

    /**
     * Verify confirmation code
     */
    public function verify(string $email, string $code): ?array
    {
        $key = self::REDIS_PREFIX . $email;
        $value = Redis::get($key);

        if (!$value) {
            return null;
        }

        $stored = json_decode($value, true);

        if ($stored['code'] !== $code) {
            return null;
        }

        // Delete after verification
        Redis::del($key);

        return $stored['data'];
    }

    /**
     * Check if verification code exists
     */
    public function exists(string $email): bool
    {
        $key = self::REDIS_PREFIX . $email;
        return Redis::exists($key) > 0;
    }

    /**
     * Get time remaining for verification
     */
    public function getTimeRemaining(string $email): ?int
    {
        $key = self::REDIS_PREFIX . $email;
        return Redis::ttl($key);
    }

    /**
     * Revoke verification code
     */
    public function revoke(string $email): bool
    {
        $key = self::REDIS_PREFIX . $email;
        return Redis::del($key) > 0;
    }
}
