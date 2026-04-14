<?php

namespace App\Services\MockHandler;

use App\Models\MockCredential;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ApiKeyStrategy implements AuthStrategyInterface
{
    public function validate(Request $request, array $config, Collection $credentials): ?MockCredential
    {
        $location = $config['location'] ?? 'header';

        $providedKey = match ($location) {
            'header' => $request->header($config['header_name'] ?? 'X-API-Key', ''),
            'query' => $request->query($config['query_param'] ?? 'api_key', ''),
            'body' => $request->input($config['body_field'] ?? 'api_key', ''),
            default => '',
        };

        if (empty($providedKey)) {
            return null;
        }

        foreach ($credentials as $credential) {
            $creds = $credential->credentials ?? [];
            if (($creds['key'] ?? '') === $providedKey) {
                return $credential;
            }
        }

        return null;
    }

    public function getTokenEndpointResponse(Request $request, array $config, Collection $credentials): array
    {
        $location = $config['location'] ?? 'header';
        $headerName = $config['header_name'] ?? 'X-API-Key';

        return [
            'status' => 200,
            'body' => [
                'message' => 'API Key auth does not use token endpoints. Include your key in requests.',
                'location' => $location,
                'example' => match ($location) {
                    'header' => "{$headerName}: <your-api-key>",
                    'query' => "?api_key=<your-api-key>",
                    'body' => json_encode(['api_key' => '<your-api-key>']),
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
