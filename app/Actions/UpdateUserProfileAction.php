<?php

namespace App\Actions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

use App\Models\User;

/** Действие обновления профиля пользователя: имя, email и аватар */
class UpdateUserProfileAction
{
    /** Применяет изменения профиля и обнуляет email_verified_at при смене адреса */
    public function execute(User $user, array $data): bool
    {
        if (isset($data['name'])) {
            $user->name = trim($data['name']);
        }

        if (array_key_exists('surname', $data)) {
            $user->surname = $data['surname'] ? trim($data['surname']) : null;
        }

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $user->email = $data['email'];
            $user->email_verified_at = null;
        }

        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $path = $data['avatar']->store('profile_photos', 'public');
            $user->avatar_path = $path;
        }
        return $user->save();
    }
}
