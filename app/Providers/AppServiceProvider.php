<?php

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(JsonResponse::class, function ($app, $parameters) {
            $response = new JsonResponse(
                $parameters['data'] ?? [],
                $parameters['status'] ?? 200,
                $parameters['headers'] ?? []
            );

            $response->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $response;
        });
    }
}
