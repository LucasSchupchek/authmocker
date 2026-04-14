<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMockCredentialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'credentials' => ['sometimes', 'array'],
            'profile' => ['sometimes', 'array'],
            'profile.name' => ['sometimes', 'string', 'max:255'],
            'profile.email' => ['sometimes', 'nullable', 'email'],
            'profile.role' => ['sometimes', 'nullable', 'string'],
            'profile.custom' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
