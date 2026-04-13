<?php

namespace App\Http\Requests;

use App\Enums\AuthType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMockServerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes', 'string', 'max:100', 'alpha_dash',
                Rule::unique('mock_servers', 'slug')->ignore($this->route('server')),
            ],
            'auth_type' => ['sometimes', 'string', Rule::in(array_column(AuthType::cases(), 'value'))],
            'config' => ['sometimes', 'array'],
            'is_active' => ['sometimes', 'boolean'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
