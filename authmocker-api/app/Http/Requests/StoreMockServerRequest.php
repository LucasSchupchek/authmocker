<?php

namespace App\Http\Requests;

use App\Enums\AuthType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMockServerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:mock_servers,slug'],
            'auth_type' => ['required', 'string', Rule::in(array_column(AuthType::cases(), 'value'))],
            'config' => ['sometimes', 'array'],
            'is_active' => ['sometimes', 'boolean'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
