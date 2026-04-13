<?php

namespace App\Services\MockHandler;

use Illuminate\Http\Request;

class ApiKeyStrategy implements AuthStrategyInterface
{
    public function validate(Request $request, array $config): bool
    {
        $expectedKey = $config['key'] ?? '';
        $location = $config['location'] ?? 'header';

        $providedKey = match ($location) {
            'header' => $request->header($config['header_name'] ?? 'X-API-Key', ''),
            'query' => $request->query($config['query_param'] ?? 'api_key', ''),
            'body' => $request->input($config['body_field'] ?? 'api_key', ''),
            default => '',
        };

        return $providedKey === $expectedKey;
    }

    public function getTokenEndpointResponse(Request $request, array $config): array
    {
        $location = $config['location'] ?? 'header';
        $headerName = $config['header_name'] ?? 'X-API-Key';

        return [
            'status' => 200,
            'body' => [
                'message' => 'API Key auth does not use token endpoints. Include your key in requests.',
                'location' => $location,
                'example' => match ($location) {
                    'header' => "{$headerName}: {$config['key']}",
                    'query' => "?api_key={$config['key']}",
                    'body' => json_encode(['api_key' => $config['key']]),
                    default => '',
                },
            ],
        ];
    }

    public function getErrorResponse(): array
    {
        return [
            'status' => 401,
            'body' => [
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing API key.',
            ],
        ];
    }
}
