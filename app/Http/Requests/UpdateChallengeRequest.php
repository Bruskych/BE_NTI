<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Запрос обновления данных задачи компании */
class UpdateChallengeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'program_id'              => 'sometimes|required|exists:programs,id',
            'title'                   => 'sometimes|required|string|max:255',
            'description'             => 'sometimes|required|string',
            'technical_specification' => 'nullable|string',
            'budget'                  => 'nullable|numeric|min:0',
            'product_owner_id'        => 'nullable|exists:users,id',
            'deadline'                => 'sometimes|required|date',
            'status'                  => 'sometimes|required|in:draft,published,pairing,assigned,active,closed',
            'max_applications'        => 'sometimes|required|integer|min:1',
            'backlog_order'           => 'nullable|integer',
            'specialization_ids'      => 'sometimes|array',
            'specialization_ids.*'    => 'exists:specializations,id',
        ];
    }
}
