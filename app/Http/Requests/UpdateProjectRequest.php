<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id' => 'sometimes|exists:applications,id',
            'title'          => 'sometimes|string|max:255',
            'description'    => 'nullable|string',
            'status'         => 'nullable|string',
        ];
    }
}
