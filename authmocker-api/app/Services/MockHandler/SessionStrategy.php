<?php

namespace App\Services\MockHandler;

use App\Models\MockCredential;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SessionStrategy implements AuthStrategyInterface
{
    public function validate(Request $request, array $config, Collection $credentials): ?MockCredential
    {
        $cookieName = $config['cookie_name'] ?? 'mock_session';
        $sessionToken = $request->cookie($cookieName) ?? $request->header('X-Session-Token', '');

        if (empty($sessionToken)) {
            return null;
        }

        // Sessions live in the cache so they survive across requests; the cache TTL handles expiry.
        $session = Cache::get($this->getCacheKey($config, $sessionToken));

        if (!is_array($session)) {
            return null;
        }

        // Find the credential that was stored in the session
        $credentialId = $session['credential_id'] ?? null;

        if ($credentialId && $credentials->isNotEmpty()) {
            $matched = $credentials->firstWhere('id', $credentialId);
            if ($matched) {
                return $matched;
            }
        }

        return $credentials->first();
    }

    public function getTokenEndpointResponse(Request $request, array $config, Collection $credentials): array
    {
        $username = $request->input('username', '');
        $password = $request->input('password', '');

        // Iterate credentials to match username/password
        $matchedCredential = null;
        foreach ($credentials as $credential) {
            $creds = $credential->credentials ?? [];
            if (($creds['username'] ?? '') === $username && ($creds['password'] ?? '') === $password) {
                $matchedCredential = $credential;
                break;
            }
        }

        if (!$matchedCredential) {
            return [
                'status' => 401,
                'body' => [
                    'error' => 'Unauthorized',
                    'message' => 'Invalid credentials.',
                ],
            ];
        }

        $sessionToken = Str::random(64);
        $cookieName = $config['cookie_name'] ?? 'mock_session';
        $ttlMinutes = $config['session_ttl_minutes'] ?? 120;

        Cache::put($this->getCacheKey($config, $sessionToken), [
            'username' => $username,
            'credential_id' => $matchedCredential->id,
            'created_at' => time(),
        ], now()->addMinutes($ttlMinutes));

        return [
            'status' => 200,
            'body' => [
                'message' => 'Login successful.',
                'session_token' => $sessionToken,
                'expires_in' => $ttlMinutes * 60,
                'matched_credential_id' => $matchedCredential->id,
            ],
            'cookies' => [
                $cookieName => [
                    'value' => $sessionToken,
                    'minutes' => $ttlMinutes,
                    'path' => '/',
                    'httpOnly' => true,
                ],
            ],
            'matched_credential_id' => $matchedCredential->id,
        ];
    }

    public function getErrorResponse(): array
    {
        return [
            'status' => 401,
            'body' => [
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired session. Please login first.',
            ],
        ];
    }

    private function getCacheKey(array $config, string $sessionToken): string
    {
        return 'mock_session:' . md5(json_encode($config)) . ':' . $sessionToken;
    }
}
