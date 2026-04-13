<?php

namespace App\Http\Requests;

use App\Enums\HttpMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMockEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => ['required', 'string', Rule::in(array_column(HttpMethod::cases(), 'value'))],
            'path' => ['required', 'string', 'max:500'],
            'response_status' => ['required', 'integer', 'min:100', 'max:599'],
            'response_body' => ['sometimes', 'nullable', 'array'],
            'response_headers' => ['sometimes', 'nullable', 'array'],
            'delay_ms' => ['sometimes', 'integer', 'min:0', 'max:30000'],
            'is_active' => ['sometimes', 'boolean'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
