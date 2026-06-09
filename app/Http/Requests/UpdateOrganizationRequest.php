<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Запрос обновления данных организации */
class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'name'         => 'sometimes|required|string|max:255',
            'tax_id'       => [
                'sometimes', 'required', 'string', 'regex:/^\d{8,10}$/',
                Rule::unique('organizations', 'tax_id')->ignore($this->route('organization'))
            ],
            'sector'       => 'sometimes|required|string|max:255',
            'website_link' => 'sometimes|required|url|max:500',
            'description'  => 'sometimes|required|string|max:2000',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
