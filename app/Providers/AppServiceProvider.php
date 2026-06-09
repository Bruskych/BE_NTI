<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

/** Провайдер приложения: регистрирует макросы Response, ограничения частоты запросов и глобальные настройки */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов в контейнере зависимостей.
     * Вызывается до boot() — здесь привязываются интерфейсы к реализациям.
     */
    public function register(): void
    {
        //
    }

    /**
     * Инициализация сервисов после загрузки всех провайдеров.
     * Здесь регистрируются макросы Response, лимиты частоты запросов и другие глобальные настройки.
     */
    public function boot(): void
    {
        // Макрос Response::api() — унифицированный JSON-ответ с поддержкой Unicode
        Response::macro('api', function ($data = [], int $status = 200, array $headers = []) {
            return response()->json($data, $status, $headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });

        // Spec 13: "Rate limiting na prihlasovanie, registráciu a kontaktné formuláre"
        // Ограничение частоты запросов для аутентификации — не более 5 попыток в минуту с одного IP
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
