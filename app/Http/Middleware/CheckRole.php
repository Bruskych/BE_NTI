<?php

// Если акаунта нет или роль пользователя не совпадает -
// вылазит сообщение что доступ запрещен

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => __('messages.access_denied')
            ], 403);
        }
        foreach ($roles as $role) {
            if (str_starts_with($role, '!')) {
                $negativeRole = substr($role, 1);
                if ($user->hasRole($negativeRole)) {
                    return response()->json([
                        'message' => __('messages.access_denied')
                    ], 403);
                }
                continue;
            }
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }
        return response()->json([
            'message' => __('messages.access_denied')
        ], 403);
    }
}
