<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'program_id'             => 'sometimes|required|exists:programs,id',
            'title'                  => 'sometimes|required|string|max:255',
            'description'            => 'nullable|string',
            'deadline'               => 'sometimes|required|date',
            'status'                 => 'sometimes|required|in:draft,open,closed',
            'budget'                 => 'nullable|numeric|min:0',
            'evaluation_template_id' => 'nullable|exists:evaluation_templates,id',
            'specialization_ids'     => 'sometimes|required|array',
            'specialization_ids.*'   => 'exists:specializations,id',
        ];
    }
}
