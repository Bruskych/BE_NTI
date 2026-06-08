<?php

namespace App\Actions;

use App\Models\User;

class UpdateUserProfileAction
{
    public function execute(User $user, array $data): bool
    {
        // Можно добавить дополнительные проверки если нужно
        return $user->update([
            'name' => trim($data['name'])
        ]);
    }
}
