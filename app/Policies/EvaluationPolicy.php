<?php

namespace App\Policies;

use App\Models\Evaluation;
use App\Models\User;
use App\Models\Application;
use Illuminate\Auth\Access\Response;

class EvaluationPolicy
{
    public function create(User $user, Application $application): Response
    {
        if (!$user->hasRole('expert')) {
            return Response::deny('Only experts can evaluate.');
        }

        if ($application->status !== 'in_evaluation') {
            return Response::deny('Application is not ready for evaluation.');
        }

        $alreadyEvaluated = Evaluation::where('application_id', $application->id)
            ->where('evaluator_id', $user->id)
            ->exists();

        return !$alreadyEvaluated
            ? Response::allow()
            : Response::deny('You have already evaluated this application.');
    }
}
