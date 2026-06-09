<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

/** Политика доступа к программам: публичный просмотр только активных программ */
class ProgramPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Program $program): bool
    {
        return $program->is_active;
    }
}
