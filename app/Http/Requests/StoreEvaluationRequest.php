<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment'              => 'nullable|string',
            'recommendation'       => 'required|in:approve,reject',
            'scores'               => 'required|array|min:1',
            'scores.*.criteria_id' => 'required|exists:evaluation_criteria,id',
            'scores.*.score'       => 'required|numeric|min:0|max:10',
            'scores.*.comment'     => 'nullable|string',
        ];
    }
}
