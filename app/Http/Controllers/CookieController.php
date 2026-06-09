<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;

class CookieController extends Controller
{
    /**
     * Метод для установки куки файлы
     */
    public function setCookie(Request $request)
    {
        $minutes = 60 * 24 * 30; // 30 дней

        $response = response()->json([
            'status' => 'success',
            'message' => 'Cookies have been successfully set on the backend side.'
        ]);

        return $response->withCookie(cookie('user_theme', 'dark', $minutes));
    }

    /**
     * Метод для чтения куки файлов
     */
    public function getCookie(Request $request)
    {
        $theme = $request->cookie('user_theme', 'default-light');

        return response()->json([
            'current_theme' => $theme
        ]);
    }

    /**
     * Метод для удаления куки
     */
    public function deleteCookie()
    {
        $response = response()->json([
            'message' => 'Куки удалены'
        ]);

        return $response->withoutCookie('user_theme');
    }
}
