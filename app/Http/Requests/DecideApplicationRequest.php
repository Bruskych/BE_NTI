<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Запрос принятия решения по заявке (одобрение/отклонение) с обязательным комментарием */
class DecideApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => 'required|in:approve,reject',
            'comment'  => 'nullable|string',
        ];
    }
}
