<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/** Сервис генерации и верификации одноразовых кодов подтверждения email через кэш */
class EmailConfirmationService
{
    const CACHE_PREFIX = 'email_confirmation:';
    const DEFAULT_EXPIRES_IN = 3600; // 1 hour

    /** Строит ключ кэша с пространством имён по назначению, чтобы коды разных целей не конфликтовали */
    private function key(string $email, ?string $purpose = null): string
    {
        return self::CACHE_PREFIX . ($purpose ? "{$purpose}:" : '') . $email;
    }

    /** Генерирует случайный 6-символьный код и сохраняет его в кэше на 1 час */
    public function generateCode(string $email, array $data = [], ?string $purpose = null): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->key($email, $purpose), [
            'code'       => $code,
            'data'       => $data,
            'created_at' => now()->toIso8601String(),
        ], self::DEFAULT_EXPIRES_IN);

        return $code;
    }

    /** Проверяет код подтверждения и возвращает связанные данные, удаляя запись из кэша */
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

    /** Проверяет наличие действующего кода подтверждения в кэше */
    public function exists(string $email, ?string $purpose = null): bool
    {
        return Cache::has($this->key($email, $purpose));
    }

    /** Аннулирует код подтверждения, удаляя его из кэша */
    public function revoke(string $email, ?string $purpose = null): bool
    {
        return Cache::forget($this->key($email, $purpose));
    }
}
