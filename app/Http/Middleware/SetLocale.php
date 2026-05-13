<?php

// Код ищет заголовок языка (en, sk) который приходит с Фронт части,
// если он нормальный то система переключаеться на этот язык

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language', 'en');
        if (in_array($locale, ['en', 'sk'])) {
            app()->setLocale($locale);
        }
        return $next($request);
    }
}
