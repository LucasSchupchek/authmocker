<?php

namespace App\Services\MockHandler;

use App\Models\MockCredential;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class OAuth2Strategy implements AuthStrategyInterface
{
    public function validate(Request $request, array $config, Collection $credentials): ?MockCredential
    {
        $authHeader = $request->header('Authorization', '');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);

        // Try decoding against each credential's client_secret
        foreach ($credentials as $credential) {
            $creds = $credential->credentials ?? [];
            $secret = $creds['client_secret'] ?? '';

            if (empty($secret)) {
                continue;
            }

            try {
                JWT::decode($token, new Key($secret, 'HS256'));
                return $credential;
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }

    public function getTokenEndpointResponse(Request $request, array $config, Collection $credentials): array
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
            return $this->handleClientCredentials($request, $config, $credentials);
        }

        if ($grantType === 'authorization_code') {
            return $this->handleAuthorizationCode($request, $config, $credentials);
        }

        if ($grantType === 'refresh_token') {
            return $this->handleRefreshToken($request, $config, $credentials);
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

    public function getAuthorizeResponse(Request $request, array $config, Collection $credentials): array
    {
        $clientId = $request->input('client_id');
        $redirectUri = $request->input('redirect_uri');
        $scopes = $request->input('scope');
        $state = $request->input('state', '');

        // Match client_id against credentials
        $matchedCredential = null;
        foreach ($credentials as $credential) {
            $creds = $credential->credentials ?? [];
            if (($creds['client_id'] ?? '') === $clientId) {
                $matchedCredential = $credential;
                break;
            }
        }

        if (!$matchedCredential) {
            return [
                'status' => 400,
                'body' => ['error' => 'invalid_client', 'error_description' => 'Unknown client_id.'],
            ];
        }

        $matchedCreds = $matchedCredential->credentials ?? [];
        $resolvedRedirectUri = $redirectUri ?? ($matchedCreds['redirect_uri'] ?? '');
        $resolvedScopes = $scopes ?? implode(' ', $matchedCreds['scopes'] ?? []);

        $code = Str::random(32);

        $redirectUrl = $resolvedRedirectUri . '?' . http_build_query(array_filter([
            'code' => $code,
            'state' => $state,
        ]));

        return [
            'status' => 200,
            'body' => [
                'authorization_code' => $code,
                'redirect_uri' => $redirectUrl,
                'scope' => $resolvedScopes,
                'state' => $state,
                'message' => 'Use this code in the /token endpoint with grant_type=authorization_code',
            ],
            'matched_credential_id' => $matchedCredential->id,
        ];
    }

    private function handleClientCredentials(Request $request, array $config, Collection $credentials): array
    {
        $clientId = $request->input('client_id', '');
        $clientSecret = $request->input('client_secret', '');

        // Match against credentials collection
        $matchedCredential = null;
        foreach ($credentials as $credential) {
            $creds = $credential->credentials ?? [];
            if (($creds['client_id'] ?? '') === $clientId && ($creds['client_secret'] ?? '') === $clientSecret) {
                $matchedCredential = $credential;
                break;
            }
        }

        if (!$matchedCredential) {
            return [
                'status' => 401,
                'body' => [
                    'error' => 'invalid_client',
                    'error_description' => 'Client authentication failed.',
                ],
            ];
        }

        return $this->generateTokenResponse($config, $matchedCredential);
    }

    private function handleAuthorizationCode(Request $request, array $config, Collection $credentials): array
    {
        $code = $request->input('code', '');
        $clientId = $request->input('client_id', '');

        if (empty($code)) {
            return [
                'status' => 400,
                'body' => [
                    'error' => 'invalid_request',
                    'error_description' => 'Authorization code is required.',
                ],
            ];
        }

        // Match client_id against credentials
        $matchedCredential = null;
        foreach ($credentials as $credential) {
            $creds = $credential->credentials ?? [];
            if (($creds['client_id'] ?? '') === $clientId) {
                $matchedCredential = $credential;
                break;
            }
        }

        if (!$matchedCredential && $credentials->isNotEmpty()) {
            $matchedCredential = $credentials->first();
        }

        return $this->generateTokenResponse($config, $matchedCredential);
    }

    private function handleRefreshToken(Request $request, array $config, Collection $credentials): array
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

        // Try to decode the refresh token against each credential's secret
        $matchedCredential = null;
        foreach ($credentials as $credential) {
            $creds = $credential->credentials ?? [];
            $secret = $creds['client_secret'] ?? '';

            if (empty($secret)) {
                continue;
            }

            try {
                JWT::decode($refreshToken, new Key($secret, 'HS256'));
                $matchedCredential = $credential;
                break;
            } catch (Throwable) {
                continue;
            }
        }

        if (!$matchedCredential && $credentials->isNotEmpty()) {
            $matchedCredential = $credentials->first();
        }

        return $this->generateTokenResponse($config, $matchedCredential);
    }

    private function generateTokenResponse(array $config, ?MockCredential $matchedCredential): array
    {
        $creds = $matchedCredential?->credentials ?? [];
        $secret = $creds['client_secret'] ?? '';
        $scopes = $creds['scopes'] ?? ['read'];
        $profile = $matchedCredential?->profile ?? [];

        $accessTtl = $config['access_token_ttl'] ?? 3600;
        $refreshTtl = $config['refresh_token_ttl'] ?? 86400;
        $now = time();

        $accessPayload = array_merge($profile, [
            'iss' => 'authmocker',
            'iat' => $now,
            'exp' => $now + $accessTtl,
            'client_id' => $creds['client_id'] ?? '',
            'scope' => implode(' ', $scopes),
            'token_type' => 'access',
        ]);

        $refreshPayload = [
            'iss' => 'authmocker',
            'iat' => $now,
            'exp' => $now + $refreshTtl,
            'client_id' => $creds['client_id'] ?? '',
            'token_type' => 'refresh',
        ];

        $responseBody = [
            'access_token' => JWT::encode($accessPayload, $secret, 'HS256'),
            'token_type' => 'Bearer',
            'expires_in' => $accessTtl,
            'refresh_token' => JWT::encode($refreshPayload, $secret, 'HS256'),
            'scope' => implode(' ', $scopes),
        ];

        if ($matchedCredential) {
            $responseBody['matched_credential_id'] = $matchedCredential->id;
        }

        return [
            'status' => 200,
            'body' => $responseBody,
            'matched_credential_id' => $matchedCredential?->id,
        ];
    }
}
