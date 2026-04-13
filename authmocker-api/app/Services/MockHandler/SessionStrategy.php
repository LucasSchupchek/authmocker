<?php

namespace App\Services\MockHandler;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionStrategy implements AuthStrategyInterface
{
    private static array $activeSessions = [];

    public function validate(Request $request, array $config): bool
    {
        $cookieName = $config['cookie_name'] ?? 'mock_session';
        $sessionToken = $request->cookie($cookieName) ?? $request->header('X-Session-Token', '');

        if (empty($sessionToken)) {
            return false;
        }

        $sessionKey = $this->getSessionKey($config);

        if (!isset(self::$activeSessions[$sessionKey][$sessionToken])) {
            return false;
        }

        $session = self::$activeSessions[$sessionKey][$sessionToken];
        $ttlMinutes = $config['session_ttl_minutes'] ?? 120;

        if (time() > ($session['created_at'] + ($ttlMinutes * 60))) {
            unset(self::$activeSessions[$sessionKey][$sessionToken]);
            return false;
        }

        return true;
    }

    public function getTokenEndpointResponse(Request $request, array $config): array
    {
        $username = $request->input('username', '');
        $password = $request->input('password', '');

        if ($username !== ($config['username'] ?? '') || $password !== ($config['password'] ?? '')) {
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
            'created_at' => time(),
        ];

        return [
            'status' => 200,
            'body' => [
                'message' => 'Login successful.',
                'session_token' => $sessionToken,
                'expires_in' => $ttlMinutes * 60,
            ],
            'cookies' => [
                $cookieName => [
                    'value' => $sessionToken,
                    'minutes' => $ttlMinutes,
                    'path' => '/',
                    'httpOnly' => true,
                ],
            ],
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
