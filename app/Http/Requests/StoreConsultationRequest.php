<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Запрос создания новой консультации ментора */
class StoreConsultationRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mentorship_id' => 'required|exists:mentorships,id',
            'milestone_id'  => 'nullable|exists:milestones,id',
            'scheduled_at'  => 'required|date|after:now',
        ];
    }
}
