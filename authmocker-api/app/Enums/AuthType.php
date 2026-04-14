<?php

namespace App\Enums;

enum AuthType: string
{
    case BASIC_AUTH = 'basic_auth';
    case API_KEY = 'api_key';
    case JWT = 'jwt';
    case OAUTH2 = 'oauth2';
    case SESSION = 'session';

    public function label(): string
    {
        return match ($this) {
            self::BASIC_AUTH => 'Basic Auth',
            self::API_KEY => 'API Key',
            self::JWT => 'JWT Bearer Token',
            self::OAUTH2 => 'OAuth2',
            self::SESSION => 'Session/Cookie',
        };
    }

    /**
     * Server-level configuration (shared across all credentials).
     */
    public function defaultServerConfig(): array
    {
        return match ($this) {
            self::BASIC_AUTH => [],
            self::API_KEY => [
                'location' => 'header',
                'header_name' => 'X-API-Key',
            ],
            self::JWT => [
                'secret' => bin2hex(random_bytes(32)),
                'algorithm' => 'HS256',
                'expiration_minutes' => 60,
            ],
            self::OAUTH2 => [
                'grant_types' => ['authorization_code', 'client_credentials'],
                'access_token_ttl' => 3600,
                'refresh_token_ttl' => 86400,
            ],
            self::SESSION => [
                'session_ttl_minutes' => 120,
                'cookie_name' => 'mock_session',
            ],
        };
    }

    /**
     * Default credential data for a new mock server.
     */
    public function defaultCredential(): array
    {
        return match ($this) {
            self::BASIC_AUTH => [
                'credentials' => [
                    'username' => 'admin',
                    'password' => 'password',
                ],
                'profile' => [
                    'name' => 'Admin User',
                    'email' => 'admin@example.com',
                    'role' => 'admin',
                ],
            ],
            self::API_KEY => [
                'credentials' => [
                    'key' => bin2hex(random_bytes(16)),
                ],
                'profile' => [
                    'name' => 'Default API Client',
                    'role' => 'client',
                ],
            ],
            self::JWT => [
                'credentials' => [
                    'sub' => 'mock-user',
                ],
                'profile' => [
                    'name' => 'Mock User',
                    'email' => 'user@example.com',
                    'role' => 'user',
                ],
            ],
            self::OAUTH2 => [
                'credentials' => [
                    'client_id' => bin2hex(random_bytes(8)),
                    'client_secret' => bin2hex(random_bytes(16)),
                    'redirect_uri' => 'http://localhost:3000/callback',
                    'scopes' => ['read', 'write'],
                ],
                'profile' => [
                    'name' => 'Default OAuth Client',
                    'role' => 'client',
                ],
            ],
            self::SESSION => [
                'credentials' => [
                    'username' => 'admin',
                    'password' => 'password',
                ],
                'profile' => [
                    'name' => 'Admin User',
                    'email' => 'admin@example.com',
                    'role' => 'admin',
                ],
            ],
        };
    }

    /**
     * Backward-compatible alias for defaultServerConfig().
     */
    public function defaultConfig(): array
    {
        return $this->defaultServerConfig();
    }
}
