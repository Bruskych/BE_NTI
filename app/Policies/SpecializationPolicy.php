<?php

namespace App\Policies;

use App\Models\Specialization;
use App\Models\User;

class SpecializationPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Specialization $specialization): bool
    {
        return true;
    }
}
