<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Запрос создания новой публикации блога */
class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'         => 'nullable|in:article,faq,success_story',
            'title'        => 'required|string|max:255',
            'slug'         => 'required|string|unique:posts,slug',
            'content'      => 'required|string',
            'excerpt'      => 'nullable|string',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ];
    }
}
