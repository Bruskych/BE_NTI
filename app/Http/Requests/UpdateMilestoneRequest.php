<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Запрос обновления контрольной точки проекта */
class UpdateMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                 => 'sometimes|required|string|max:255',
            'description'           => 'nullable|string',
            'completion_percentage' => 'sometimes|required|integer|min:0|max:100',
            'status'                => 'sometimes|required|string|in:pending,in_progress,completed',
        ];
    }
}
