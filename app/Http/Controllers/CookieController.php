<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CookieController extends Controller
{
    private const COOKIE_TTL = 60 * 24 * 365; // 1 рік

    /** Зберігає налаштування теми та мови для поточного користувача */
    public function setCookie(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme' => 'nullable|in:light,dark',
            'lang'  => 'nullable|in:sk,en',
        ]);

        $response = response()->json([
            'message' => 'Preferences saved.',
            'data'    => array_filter($validated),
        ]);

        if (isset($validated['theme'])) {
            $response->withCookie(cookie('user_theme', $validated['theme'], self::COOKIE_TTL, '/', null, false, false));
        }

        if (isset($validated['lang'])) {
            $response->withCookie(cookie('user_lang', $validated['lang'], self::COOKIE_TTL, '/', null, false, false));
        }

        return $response;
    }

    /** Повертає збережені налаштування теми та мови */
    public function getCookie(Request $request): JsonResponse
    {
        return response()->json([
            'theme' => $request->cookie('user_theme', 'light'),
            'lang'  => $request->cookie('user_lang', 'sk'),
        ]);
    }

    /** Видаляє збережені налаштування */
    public function deleteCookie(): JsonResponse
    {
        return response()->json(['message' => 'Preferences cleared.'])
            ->withoutCookie('user_theme')
            ->withoutCookie('user_lang');
    }
}
