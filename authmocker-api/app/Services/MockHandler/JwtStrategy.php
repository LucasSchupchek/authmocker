<?php

namespace App\Services\MockHandler;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Throwable;

class JwtStrategy implements AuthStrategyInterface
{
    public function validate(Request $request, array $config): bool
    {
        $authHeader = $request->header('Authorization', '');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return false;
        }

        $token = substr($authHeader, 7);
        $secret = $config['secret'] ?? '';
        $algorithm = $config['algorithm'] ?? 'HS256';

        try {
            JWT::decode($token, new Key($secret, $algorithm));
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function getTokenEndpointResponse(Request $request, array $config): array
    {
        $secret = $config['secret'] ?? '';
        $algorithm = $config['algorithm'] ?? 'HS256';
        $expirationMinutes = $config['expiration_minutes'] ?? 60;
        $customClaims = $config['claims'] ?? [];

        $now = time();
        $payload = array_merge($customClaims, [
            'iss' => 'authmocker',
            'iat' => $now,
            'exp' => $now + ($expirationMinutes * 60),
            'sub' => $request->input('sub', 'mock-user'),
        ]);

        $token = JWT::encode($payload, $secret, $algorithm);

        return [
            'status' => 200,
            'body' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $expirationMinutes * 60,
                'claims' => $payload,
            ],
        ];
    }

    public function getErrorResponse(): array
    {
        return [
            'status' => 401,
            'body' => [
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired JWT token.',
            ],
        ];
    }
}
