<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Запрос обновления данных менторства */
class UpdateMentorshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'      => 'sometimes|string|in:active,finished',
            'finished_at' => 'nullable|date',
        ];
    }
}
