<?php

namespace App\Services\MockHandler;

use Illuminate\Http\Request;

class BasicAuthStrategy implements AuthStrategyInterface
{
    public function validate(Request $request, array $config): bool
    {
        $authHeader = $request->header('Authorization', '');

        if (!str_starts_with($authHeader, 'Basic ')) {
            return false;
        }

        $decoded = base64_decode(substr($authHeader, 6));
        if ($decoded === false) {
            return false;
        }

        $parts = explode(':', $decoded, 2);
        if (count($parts) !== 2) {
            return false;
        }

        [$username, $password] = $parts;

        return $username === ($config['username'] ?? '') && $password === ($config['password'] ?? '');
    }

    public function getTokenEndpointResponse(Request $request, array $config): array
    {
        return [
            'status' => 200,
            'body' => [
                'message' => 'Basic Auth does not use token endpoints. Send credentials via Authorization header.',
                'example' => 'Authorization: Basic ' . base64_encode(($config['username'] ?? 'user') . ':' . ($config['password'] ?? 'pass')),
            ],
        ];
    }

    public function getErrorResponse(): array
    {
        return [
            'status' => 401,
            'body' => [
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing Basic Auth credentials.',
            ],
            'headers' => [
                'WWW-Authenticate' => 'Basic realm="AuthMocker"',
            ],
        ];
    }
}
