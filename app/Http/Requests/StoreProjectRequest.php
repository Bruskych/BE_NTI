<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Запрос создания нового проекта из одобренной заявки */
class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id' => 'required|exists:applications,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
        ];
    }
}
