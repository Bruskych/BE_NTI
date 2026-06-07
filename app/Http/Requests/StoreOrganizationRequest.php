<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'tax_id'       => 'required|string|regex:/^\d{8,10}$/|unique:organizations,tax_id',
            'sector'       => 'required|string|max:255',
            'website_link' => 'required|url|max:500',
            'description'  => 'required|string|max:2000',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
