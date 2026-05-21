<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    // Главная страница
    public function dashboard(Request $request)
    {
        return response()->json([
            'users_count' => User::count(),
            'notifications_count' => Notification::count(),
            'latest_users' => User::latest()->take(5)->get(),
        ]);
    }

    // Список пользователей
    public function users()
    {
        return response()->json(
            User::with('roles')->paginate(20)
        );
    }

    // Список уведомлений
    public function notifications()
    {
        return response()->json(
            Notification::latest()->paginate(20)
        );
    }

    // Получить заявки студентов
    public function pendingStudents()
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'student');
        })
            ->where('status', 'pending')
            ->get();
    }

    // Одобрить студента
    public function approveStudent(User $user)
    {
        $user->update([
            'status' => 'active'
        ]);
        return response()->json(['message' => 'Approved']);
    }

    // Отклонить студента
    public function rejectStudent(User $user)
    {
        $user->update([
            'status' => 'rejected'
        ]);
        return response()->json(['message' => 'Rejected']);
    }
}
