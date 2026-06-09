<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Запрос обновления публикации блога */
class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => 'sometimes|string|max:255',
            'slug'         => 'sometimes|string|unique:posts,slug,' . $this->post->id,
            'content'      => 'sometimes|string',
            'is_published' => 'sometimes|boolean',
        ];
    }
}
