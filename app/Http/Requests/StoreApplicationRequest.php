<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Запрос создания новой заявки на участие в программе */
class StoreApplicationRequest extends FormRequest
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
            'program_id'   => 'required|exists:programs,id',
            'call_id'      => 'nullable|required_without:challenge_id|exists:calls,id',
            'challenge_id' => 'nullable|required_without:call_id|exists:challenges,id',
        ];
    }
}
