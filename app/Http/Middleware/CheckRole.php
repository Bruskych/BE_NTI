<?php

// Если акаунта нет или роль пользователя не совпадает -
// вылазит сообщение что доступ запрещен

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            return response()->json(['message' => __('messages.access_denied')], 403);
        }
        return $next($request);
    }
}
