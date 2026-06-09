<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Запрос обновления данных заявки (ответы на поля формы) */
class UpdateApplicationRequest extends FormRequest
{

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
        // Spec 13: "Antivírusová alebo aspoň MIME / príponová kontrola uploadovaných príloh"
        $fileRule = 'nullable|file|mimes:' . Document::ALLOWED_UPLOAD_EXTENSIONS . '|max:10240';

        return [
            'organization_id' => 'sometimes|exists:organizations,id',

            // Program-configurable form answers (saved progressively while the application is a draft)
            'answers'                  => 'sometimes|array',
            'answers.*.field_id'       => 'required_with:answers|integer|exists:form_fields,id',
            'answers.*.value_text'     => 'nullable|string',
            'answers.*.value_json'     => 'nullable|array',
            'answers.*.file'           => $fileRule,

            // Program B pairing submissions (CV, motivation letter, solution proposal)
            'pairing_submissions'              => 'sometimes|array',
            'pairing_submissions.*.type'       => 'required_with:pairing_submissions|in:cv,motivation_letter,solution_proposal,other',
            'pairing_submissions.*.file'       => $fileRule,
            'pairing_submissions.*.notes'      => 'nullable|string',
        ];
    }
}
