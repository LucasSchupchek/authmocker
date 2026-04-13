<?php

namespace App\Services\MockHandler;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class OAuth2Strategy implements AuthStrategyInterface
{
    public function validate(Request $request, array $config): bool
    {
        $authHeader = $request->header('Authorization', '');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return false;
        }

        $token = substr($authHeader, 7);
        $secret = $config['client_secret'] ?? '';

        try {
            JWT::decode($token, new Key($secret, 'HS256'));
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function getTokenEndpointResponse(Request $request, array $config): array
    {
        $grantType = $request->input('grant_type', 'client_credentials');
        $allowedGrants = $config['grant_types'] ?? ['client_credentials'];

        if (!in_array($grantType, $allowedGrants)) {
            return [
                'status' => 400,
                'body' => [
                    'error' => 'unsupported_grant_type',
                    'error_description' => "Grant type '{$grantType}' is not supported. Allowed: " . implode(', ', $allowedGrants),
                ],
            ];
        }

        if ($grantType === 'client_credentials') {
            return $this->handleClientCredentials($request, $config);
        }

        if ($grantType === 'authorization_code') {
            return $this->handleAuthorizationCode($request, $config);
        }

        if ($grantType === 'refresh_token') {
            return $this->handleRefreshToken($request, $config);
        }

        return [
            'status' => 400,
            'body' => ['error' => 'unsupported_grant_type'],
        ];
    }

    public function getErrorResponse(): array
    {
        return [
            'status' => 401,
            'body' => [
                'error' => 'invalid_token',
                'error_description' => 'The access token is invalid or has expired.',
            ],
        ];
    }

    public function getAuthorizeResponse(Request $request, array $config): array
    {
        $clientId = $request->input('client_id');
        $redirectUri = $request->input('redirect_uri', $config['redirect_uri'] ?? '');
        $scopes = $request->input('scope', implode(' ', $config['scopes'] ?? []));
        $state = $request->input('state', '');

        if ($clientId !== ($config['client_id'] ?? '')) {
            return [
                'status' => 400,
                'body' => ['error' => 'invalid_client', 'error_description' => 'Unknown client_id.'],
            ];
        }

        $code = Str::random(32);

        $redirectUrl = $redirectUri . '?' . http_build_query(array_filter([
            'code' => $code,
            'state' => $state,
        ]));

        return [
            'status' => 200,
            'body' => [
                'authorization_code' => $code,
                'redirect_uri' => $redirectUrl,
                'scope' => $scopes,
                'state' => $state,
                'message' => 'Use this code in the /token endpoint with grant_type=authorization_code',
            ],
        ];
    }

    private function handleClientCredentials(Request $request, array $config): array
    {
        $clientId = $request->input('client_id', '');
        $clientSecret = $request->input('client_secret', '');

        if ($clientId !== ($config['client_id'] ?? '') || $clientSecret !== ($config['client_secret'] ?? '')) {
            return [
                'status' => 401,
                'body' => [
                    'error' => 'invalid_client',
                    'error_description' => 'Client authentication failed.',
                ],
            ];
        }

        return $this->generateTokenResponse($config);
    }

    private function handleAuthorizationCode(Request $request, array $config): array
    {
        $code = $request->input('code', '');

        if (empty($code)) {
            return [
                'status' => 400,
                'body' => [
                    'error' => 'invalid_request',
                    'error_description' => 'Authorization code is required.',
                ],
            ];
        }

        return $this->generateTokenResponse($config);
    }

    private function handleRefreshToken(Request $request, array $config): array
    {
        $refreshToken = $request->input('refresh_token', '');

        if (empty($refreshToken)) {
            return [
                'status' => 400,
                'body' => [
                    'error' => 'invalid_request',
                    'error_description' => 'Refresh token is required.',
                ],
            ];
        }

        return $this->generateTokenResponse($config);
    }

    private function generateTokenResponse(array $config): array
    {
        $secret = $config['client_secret'] ?? '';
        $accessTtl = $config['access_token_ttl'] ?? 3600;
        $refreshTtl = $config['refresh_token_ttl'] ?? 86400;
        $scopes = $config['scopes'] ?? ['read'];
        $now = time();

        $accessPayload = [
            'iss' => 'authmocker',
            'iat' => $now,
            'exp' => $now + $accessTtl,
            'client_id' => $config['client_id'] ?? '',
            'scope' => implode(' ', $scopes),
            'token_type' => 'access',
        ];

        $refreshPayload = [
            'iss' => 'authmocker',
            'iat' => $now,
            'exp' => $now + $refreshTtl,
            'client_id' => $config['client_id'] ?? '',
            'token_type' => 'refresh',
        ];

        return [
            'status' => 200,
            'body' => [
                'access_token' => JWT::encode($accessPayload, $secret, 'HS256'),
                'token_type' => 'Bearer',
                'expires_in' => $accessTtl,
                'refresh_token' => JWT::encode($refreshPayload, $secret, 'HS256'),
                'scope' => implode(' ', $scopes),
            ],
        ];
    }
}
