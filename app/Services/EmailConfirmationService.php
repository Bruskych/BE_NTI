<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EmailConfirmationService
{
    const CACHE_PREFIX = 'email_confirmation:';
    const DEFAULT_EXPIRES_IN = 3600; // 1 hour

    /**
     * Namespaces the cache key by purpose so e.g. a registration "email_verification"
     * code can't collide with (or be overwritten by) a "document_access" code
     * requested for the same address.
     */
    private function key(string $email, ?string $purpose = null): string
    {
        return self::CACHE_PREFIX . ($purpose ? "{$purpose}:" : '') . $email;
    }

    /**
     * Generate a confirmation code and store it in the application cache
     * (backed by Redis when CACHE_STORE=redis, but driver-agnostic otherwise —
     * keeps this service usable without a phpredis/predis client installed).
     */
    public function generateCode(string $email, array $data = [], ?string $purpose = null): string
    {
        $code = Str::random(6);

        Cache::put($this->key($email, $purpose), [
            'code'       => $code,
            'data'       => $data,
            'created_at' => now()->toIso8601String(),
        ], self::DEFAULT_EXPIRES_IN);

        return $code;
    }

    /**
     * Verify confirmation code
     */
    public function verify(string $email, string $code, ?string $purpose = null): ?array
    {
        $key = $this->key($email, $purpose);
        $stored = Cache::get($key);

        if (!$stored || $stored['code'] !== $code) {
            return null;
        }

        // Delete after verification
        Cache::forget($key);

        return $stored['data'];
    }

    /**
     * Check if a (still valid) confirmation code exists
     */
    public function exists(string $email, ?string $purpose = null): bool
    {
        return Cache::has($this->key($email, $purpose));
    }

    /**
     * Revoke confirmation code
     */
    public function revoke(string $email, ?string $purpose = null): bool
    {
        return Cache::forget($this->key($email, $purpose));
    }
}
