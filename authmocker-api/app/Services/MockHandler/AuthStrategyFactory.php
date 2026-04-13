<?php

namespace App\Services\MockHandler;

use App\Enums\AuthType;
use InvalidArgumentException;

class AuthStrategyFactory
{
    public static function make(AuthType $type): AuthStrategyInterface
    {
        return match ($type) {
            AuthType::BASIC_AUTH => new BasicAuthStrategy(),
            AuthType::API_KEY => new ApiKeyStrategy(),
            AuthType::JWT => new JwtStrategy(),
            AuthType::OAUTH2 => new OAuth2Strategy(),
            AuthType::SESSION => new SessionStrategy(),
        };
    }
}
