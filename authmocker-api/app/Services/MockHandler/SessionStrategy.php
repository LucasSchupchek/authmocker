<?php

namespace App\Services\MockHandler;

use App\Models\MockCredential;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionStrategy implements AuthStrategyInterface
{
    private static array $activeSessions = [];

    public function validate(Request $request, array $config, Collection $credentials): ?MockCredential
    {
        $cookieName = $config['cookie_name'] ?? 'mock_session';
        $sessionToken = $request->cookie($cookieName) ?? $request->header('X-Session-Token', '');

        if (empty($sessionToken)) {
            return null;
        }

        $sessionKey = $this->getSessionKey($config);

        if (!isset(self::$activeSessions[$sessionKey][$sessionToken])) {
            return null;
        }

        $session = self::$activeSessions[$sessionKey][$sessionToken];
        $ttlMinutes = $config['session_ttl_minutes'] ?? 120;

        if (time() > ($session['created_at'] + ($ttlMinutes * 60))) {
            unset(self::$activeSessions[$sessionKey][$sessionToken]);
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
        $sessionKey = $this->getSessionKey($config);

        self::$activeSessions[$sessionKey][$sessionToken] = [
            'username' => $username,
            'credential_id' => $matchedCredential->id,
            'created_at' => time(),
        ];

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

    private function getSessionKey(array $config): string
    {
        return md5(json_encode($config));
    }
}
