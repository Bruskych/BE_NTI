<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExportRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'export_type' => 'required|string|in:users_csv,projects_xlsx,applications_pdf',
            'filters'     => 'nullable|array',
        ];
    }
}
