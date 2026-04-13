<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization', '');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Missing or invalid Authorization header.',
            ], 401);
        }

        $token = substr($authHeader, 7);

        try {
            $user = $this->authService->getUserFromToken($token);
            $request->merge(['user_id' => $user->id]);
            $request->attributes->set('auth_user', $user);
        } catch (RuntimeException) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired token.',
            ], 401);
        }

        return $next($request);
    }
}
