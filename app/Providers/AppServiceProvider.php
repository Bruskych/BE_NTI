<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    public function boot(): void
    {
        Response::macro('api', function ($data = [], int $status = 200, array $headers = []) {
            return response()->json($data, $status, $headers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });
    }
}
