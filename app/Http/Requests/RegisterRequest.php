<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Запрос регистрации нового пользователя (студент или компания) с GDPR-согласием */
class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'email'            => 'required|string|email|max:255|unique:users',
            'password'         => 'required|string|min:8|confirmed',
            'role'             => 'required|string|in:student,company,mentor',
            'gdpr_consent'     => 'accepted',
            'consent_version'  => 'sometimes|string|max:50',
            'company_name'     => 'required_if:role,company|string|max:255',
            'company_tax_id'   => 'required_if:role,company|string|regex:/^\d{8,10}$/',
            'sector'           => 'required_if:role,company|string|max:255',
            'website_link'     => 'required_if:role,company|url|max:500',
            'description'      => 'required_if:role,company|string|max:2000',
        ];
    }
}
