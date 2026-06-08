<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMentorshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'mentor_id'  => 'required|exists:users,id',
            'status'     => 'required|string|in:active,finished',
        ];
    }
}
