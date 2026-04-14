<?php

namespace App\Services\MockHandler;

use App\Models\MockCredential;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Throwable;

class JwtStrategy implements AuthStrategyInterface
{
    public function validate(Request $request, array $config, Collection $credentials): ?MockCredential
    {
        $authHeader = $request->header('Authorization', '');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);
        $secret = $config['secret'] ?? '';
        $algorithm = $config['algorithm'] ?? 'HS256';

        try {
            $decoded = JWT::decode($token, new Key($secret, $algorithm));
            $sub = $decoded->sub ?? null;

            if ($sub && $credentials->isNotEmpty()) {
                foreach ($credentials as $credential) {
                    $creds = $credential->credentials ?? [];
                    if (($creds['sub'] ?? '') === $sub) {
                        return $credential;
                    }
                }
            }

            // Token is valid but no matching credential found - return first active credential
            return $credentials->first();
        } catch (Throwable) {
            return null;
        }
    }

    public function getTokenEndpointResponse(Request $request, array $config, Collection $credentials): array
    {
        $secret = $config['secret'] ?? '';
        $algorithm = $config['algorithm'] ?? 'HS256';
        $expirationMinutes = $config['expiration_minutes'] ?? 60;

        // Find matching credential by sub or credential_id
        $matchedCredential = null;
        $sub = $request->input('sub');
        $credentialId = $request->input('credential_id');

        if ($credentialId && $credentials->isNotEmpty()) {
            $matchedCredential = $credentials->firstWhere('id', $credentialId);
        }

        if (!$matchedCredential && $sub && $credentials->isNotEmpty()) {
            foreach ($credentials as $credential) {
                $creds = $credential->credentials ?? [];
                if (($creds['sub'] ?? '') === $sub) {
                    $matchedCredential = $credential;
                    break;
                }
            }
        }

        if (!$matchedCredential && $credentials->isNotEmpty()) {
            $matchedCredential = $credentials->first();
        }

        $credSub = $matchedCredential ? ($matchedCredential->credentials['sub'] ?? $sub ?? 'mock-user') : ($sub ?? 'mock-user');
        $profile = $matchedCredential?->profile ?? [];

        $now = time();
        $payload = array_merge($profile, [
            'iss' => 'authmocker',
            'iat' => $now,
            'exp' => $now + ($expirationMinutes * 60),
            'sub' => $credSub,
        ]);

        $token = JWT::encode($payload, $secret, $algorithm);

        $responseBody = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expirationMinutes * 60,
            'claims' => $payload,
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
