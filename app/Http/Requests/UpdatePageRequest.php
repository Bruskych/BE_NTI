<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => 'sometimes|string|max:255',
            'slug'             => 'sometimes|string|unique:pages,slug,' . $this->page->id,
            'content'          => 'sometimes|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_published'     => 'sometimes|boolean',
        ];
    }
}
