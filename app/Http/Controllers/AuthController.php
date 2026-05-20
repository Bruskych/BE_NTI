<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            // Проверка что роль одна из допустимых
            'role'     => 'required|string|in:student,company,mentor'
        ]);

        // Создание пользователя без роли
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Назначаем роль
        $user->assignRole($request->role);

        // Можно создать пустой профиль
        if ($request->role === 'student') {
            $user->studentProfile()->create();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Возвращаем пользователя вместе с его ролями для фронта
        return response()->json([
            'token' => $token,
            'user'  => $user->load('roles'),
        ], 201);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный логин или пароль.'],
            ]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token, // Загрузка токена на фронт
            'user'  => $user->load('roles'), // Загрузка ролей на фронт
            'notifications' => $user->notifications()->latest()->get(), // Загрузка уведомлений на фронт
        ]);
    }
    public function me(Request $request)
    {
        // Мы берем текущего юзера из запроса (его туда положил Sanctum)
        $user = $request->user();

        // Подгружаем роли, чтобы на фронте работали проверки прав
        $user->load('roles');

        return response()->json([
            'user' => $user
        ]);
    }
    public function logout(Request $request)
    {
        // Удаление токена который был использован для этого запроса
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Сессия успешно завершена'
        ]);
    }
}
