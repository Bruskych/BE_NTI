<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Запрос создания новой задачи компании */
class StoreChallengeRequest extends FormRequest
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
            'program_id'              => 'required|exists:programs,id',
            'organization_id'         => 'nullable|exists:organizations,id',
            'title'                   => 'required|string|max:255',
            'description'             => 'required|string',
            'technical_specification' => 'nullable|string',
            'budget'                  => 'nullable|numeric|min:0',
            'product_owner_id'        => 'nullable|exists:users,id',
            'deadline'                => 'required|date|after:now',
            'status'                  => 'required|in:draft,published,pairing,assigned,active,closed',
            'max_applications'        => 'required|integer|min:1',
            'backlog_order'           => 'nullable|integer',
            'specialization_ids'      => 'required|array',
            'specialization_ids.*'    => 'exists:specializations,id',
        ];
    }

    protected function prepareForValidation()
    {
        if (!$this->user()->can('challenges.create-any')) {
            $organization = $this->user()->organizations()->first();

            if (!$organization) {
                abort(403, 'You dont have organization.');
            }

            $this->merge([
                'organization_id' => $organization->id,
                'product_owner_id' => $this->user()->id,
            ]);
        }
    }
}
