<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Throwable;

class AuthService
{
    private string $secret;
    private string $algorithm = 'HS256';
    private int $accessTtl;
    private int $refreshTtl;

    public function __construct()
    {
        $this->secret = config('app.key');
        $this->accessTtl = (int) config('auth.token_ttl', 3600);
        $this->refreshTtl = (int) config('auth.refresh_ttl', 604800);
    }

    public function register(string $name, string $email, string $password): User
    {
        if (User::where('email', $email)->exists()) {
            throw new RuntimeException('Email already registered.', 409);
        }

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new RuntimeException('Invalid credentials.', 401);
        }

        return [
            'user' => $user,
            'access_token' => $this->generateAccessToken($user),
            'refresh_token' => $this->generateRefreshToken($user),
            'expires_in' => $this->accessTtl,
        ];
    }

    public function refreshToken(string $refreshToken): array
    {
        $payload = $this->decodeToken($refreshToken);

        if (($payload->type ?? '') !== 'refresh') {
            throw new RuntimeException('Invalid refresh token.', 401);
        }

        $user = User::findOrFail($payload->sub);

        return [
            'access_token' => $this->generateAccessToken($user),
            'refresh_token' => $this->generateRefreshToken($user),
            'expires_in' => $this->accessTtl,
        ];
    }

    public function getUserFromToken(string $token): User
    {
        $payload = $this->decodeToken($token);

        return User::findOrFail($payload->sub);
    }

    public function decodeToken(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->secret, $this->algorithm));
        } catch (Throwable $e) {
            throw new RuntimeException('Invalid or expired token.', 401);
        }
    }

    private function generateAccessToken(User $user): string
    {
        $now = time();

        return JWT::encode([
            'iss' => config('app.url'),
            'sub' => $user->id,
            'email' => $user->email,
            'type' => 'access',
            'iat' => $now,
            'exp' => $now + $this->accessTtl,
        ], $this->secret, $this->algorithm);
    }

    private function generateRefreshToken(User $user): string
    {
        $now = time();

        return JWT::encode([
            'iss' => config('app.url'),
            'sub' => $user->id,
            'type' => 'refresh',
            'iat' => $now,
            'exp' => $now + $this->refreshTtl,
        ], $this->secret, $this->algorithm);
    }
}
