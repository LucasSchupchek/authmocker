<?php

namespace App\Services\MockHandler;

use App\Models\MockCredential;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class BasicAuthStrategy implements AuthStrategyInterface
{
    public function validate(Request $request, array $config, Collection $credentials): ?MockCredential
    {
        $authHeader = $request->header('Authorization', '');

        if (!str_starts_with($authHeader, 'Basic ')) {
            return null;
        }

        $decoded = base64_decode(substr($authHeader, 6));
        if ($decoded === false) {
            return null;
        }

        $parts = explode(':', $decoded, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$username, $password] = $parts;

        foreach ($credentials as $credential) {
            $creds = $credential->credentials ?? [];
            if (($creds['username'] ?? '') === $username && ($creds['password'] ?? '') === $password) {
                return $credential;
            }
        }

        return null;
    }

    public function getTokenEndpointResponse(Request $request, array $config, Collection $credentials): array
    {
        return [
            'status' => 200,
            'body' => [
                'message' => 'Basic Auth does not use token endpoints. Send credentials via Authorization header.',
                'example' => 'Authorization: Basic base64(username:password)',
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
