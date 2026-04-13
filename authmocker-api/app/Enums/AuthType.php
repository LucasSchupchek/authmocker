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

    public function defaultConfig(): array
    {
        return match ($this) {
            self::BASIC_AUTH => [
                'username' => 'admin',
                'password' => 'password',
            ],
            self::API_KEY => [
                'key' => bin2hex(random_bytes(16)),
                'location' => 'header',
                'header_name' => 'X-API-Key',
            ],
            self::JWT => [
                'secret' => bin2hex(random_bytes(32)),
                'algorithm' => 'HS256',
                'expiration_minutes' => 60,
                'claims' => ['role' => 'user'],
            ],
            self::OAUTH2 => [
                'client_id' => bin2hex(random_bytes(8)),
                'client_secret' => bin2hex(random_bytes(16)),
                'redirect_uri' => 'http://localhost:3000/callback',
                'grant_types' => ['authorization_code', 'client_credentials'],
                'scopes' => ['read', 'write'],
                'access_token_ttl' => 3600,
                'refresh_token_ttl' => 86400,
            ],
            self::SESSION => [
                'username' => 'admin',
                'password' => 'password',
                'session_ttl_minutes' => 120,
                'cookie_name' => 'mock_session',
            ],
        };
    }
}
