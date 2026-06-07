<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExportRequest extends FormRequest
{
    public static function availableExportOptions(): array
    {
        return [
            'users_csv' => ['resource' => 'users', 'format' => 'csv', 'label' => 'Users (CSV)'],
            'users_xlsx' => ['resource' => 'users', 'format' => 'xlsx', 'label' => 'Users (XLSX)'],
            'users_pdf' => ['resource' => 'users', 'format' => 'pdf', 'label' => 'Users (PDF)'],
            'projects_csv' => ['resource' => 'projects', 'format' => 'csv', 'label' => 'Projects (CSV)'],
            'projects_xlsx' => ['resource' => 'projects', 'format' => 'xlsx', 'label' => 'Projects (XLSX)'],
            'projects_pdf' => ['resource' => 'projects', 'format' => 'pdf', 'label' => 'Projects (PDF)'],
            'applications_csv' => ['resource' => 'applications', 'format' => 'csv', 'label' => 'Applications (CSV)'],
            'applications_xlsx' => ['resource' => 'applications', 'format' => 'xlsx', 'label' => 'Applications (XLSX)'],
            'applications_pdf' => ['resource' => 'applications', 'format' => 'pdf', 'label' => 'Applications (PDF)'],
            'personal_data_json' => ['resource' => 'personal_data', 'format' => 'json', 'label' => 'Personal data (JSON)'],
        ];
    }

    public static function allowedExportTypes(): array
    {
        return array_keys(self::availableExportOptions());
    }

    public static function availableExportOptionsGrouped(): array
    {
        $grouped = [];

        foreach (self::availableExportOptions() as $type => $option) {
            $grouped[$option['resource']][] = array_merge(['type' => $type], $option);
        }

        return $grouped;
    }

    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'export_type' => [
                'required',
                'string',
                Rule::in(self::allowedExportTypes()),
            ],
            'filters' => 'nullable|array',
            'filters.active' => 'nullable|boolean',
            'filters.status' => 'nullable|string',
        ];
    }
}
