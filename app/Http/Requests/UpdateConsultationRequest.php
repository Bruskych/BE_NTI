<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Запрос обновления данных консультации */
class UpdateConsultationRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheduled_at'    => 'sometimes|date|after:now',
            'completed_at'    => 'sometimes|nullable|date',
            'notes'           => 'sometimes|string|nullable',
            'recommendations' => 'sometimes|string|nullable',
        ];
    }
}
