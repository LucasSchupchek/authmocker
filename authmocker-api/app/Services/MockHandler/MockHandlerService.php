<?php

namespace App\Services\MockHandler;

use App\Enums\AuthStatus;
use App\Models\MockCredential;
use App\Models\MockEndpoint;
use App\Models\MockServer;
use App\Models\RequestLog;
use App\Traits\HasCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MockHandlerService
{
    use HasCache;

    private const SERVER_CACHE_TTL = 300; // 5 minutes

    public function handleRequest(Request $request, string $slug, ?string $path = null): JsonResponse
    {
        $server = $this->resolveServer($slug);

        if (!$server) {
            return response()->json([
                'error' => 'Not Found',
                'message' => "Mock server '{$slug}' not found or inactive.",
            ], 404);
        }

        $strategy = AuthStrategyFactory::make($server->auth_type);
        $config = $server->config ?? [];
        $credentials = $server->credentials;

        if ($this->isTokenEndpoint($path)) {
            return $this->handleTokenRequest($request, $server, $strategy, $config, $credentials, $path);
        }

        if ($this->isAuthorizeEndpoint($path)) {
            return $this->handleAuthorizeRequest($request, $server, $strategy, $config, $credentials);
        }

        $matchedCredential = $strategy->validate($request, $config, $credentials);
        $authStatus = $matchedCredential ? AuthStatus::SUCCESS : AuthStatus::FAILED;

        if (!$matchedCredential) {
            $errorResponse = $strategy->getErrorResponse();
            $this->logRequest($request, $server, null, $path, $authStatus, $errorResponse['status']);

            return response()->json(
                $errorResponse['body'],
                $errorResponse['status'],
                $errorResponse['headers'] ?? []
            );
        }

        $endpoint = $this->findEndpoint($server, $request->method(), $path);

        if (!$endpoint) {
            $this->logRequest($request, $server, null, $path, $authStatus, 404, $matchedCredential);

            return response()->json([
                'error' => 'Not Found',
                'message' => "Endpoint {$request->method()} /{$path} not found on this mock server.",
            ], 404);
        }

        if ($endpoint->delay_ms > 0) {
            usleep($endpoint->delay_ms * 1000);
        }

        $this->logRequest($request, $server, $endpoint, $path, $authStatus, $endpoint->response_status, $matchedCredential);

        return response()->json(
            $endpoint->response_body ?? [],
            $endpoint->response_status,
            $endpoint->response_headers ?? []
        );
    }

    private function isTokenEndpoint(?string $path): bool
    {
        return in_array($path, ['token', 'oauth/token', 'login']);
    }

    private function isAuthorizeEndpoint(?string $path): bool
    {
        return in_array($path, ['authorize', 'oauth/authorize']);
    }

    private function handleTokenRequest(
        Request $request,
        MockServer $server,
        AuthStrategyInterface $strategy,
        array $config,
        $credentials,
        ?string $path
    ): JsonResponse {
        $response = $strategy->getTokenEndpointResponse($request, $config, $credentials);

        $matchedCredentialId = $response['matched_credential_id'] ?? null;
        $matchedCredential = null;

        if ($matchedCredentialId && $credentials->isNotEmpty()) {
            $matchedCredential = $credentials->firstWhere('id', $matchedCredentialId);
        }

        $this->logRequest(
            $request, $server, null, $path,
            $response['status'] === 200 ? AuthStatus::SUCCESS : AuthStatus::FAILED,
            $response['status'],
            $matchedCredential
        );

        $jsonResponse = response()->json($response['body'], $response['status']);

        if (isset($response['cookies'])) {
            foreach ($response['cookies'] as $name => $cookie) {
                $jsonResponse->cookie(cookie(
                    $name,
                    $cookie['value'],
                    $cookie['minutes'] ?? 60,
                    $cookie['path'] ?? '/',
                    null,
                    false,
                    $cookie['httpOnly'] ?? true
                ));
            }
        }

        return $jsonResponse;
    }

    private function handleAuthorizeRequest(
        Request $request,
        MockServer $server,
        AuthStrategyInterface $strategy,
        array $config,
        $credentials
    ): JsonResponse {
        if ($strategy instanceof OAuth2Strategy) {
            $response = $strategy->getAuthorizeResponse($request, $config, $credentials);

            $matchedCredentialId = $response['matched_credential_id'] ?? null;
            $matchedCredential = null;

            if ($matchedCredentialId && $credentials->isNotEmpty()) {
                $matchedCredential = $credentials->firstWhere('id', $matchedCredentialId);
            }

            $this->logRequest($request, $server, null, 'authorize', AuthStatus::SUCCESS, $response['status'], $matchedCredential);
            return response()->json($response['body'], $response['status']);
        }

        return response()->json([
            'error' => 'Bad Request',
            'message' => 'Authorize endpoint is only available for OAuth2 servers.',
        ], 400);
    }

    private function findEndpoint(MockServer $server, string $method, ?string $path): ?MockEndpoint
    {
        $normalizedPath = ltrim($path ?? '', '/');

        return $server->endpoints()
            ->where('is_active', true)
            ->where('method', $method)
            ->where(function ($query) use ($normalizedPath) {
                $query->where('path', $normalizedPath)
                    ->orWhere('path', '/' . $normalizedPath);
            })
            ->first();
    }

    private function logRequest(
        Request $request,
        MockServer $server,
        ?MockEndpoint $endpoint,
        ?string $path,
        AuthStatus $authStatus,
        int $responseStatus,
        ?MockCredential $matchedCredential = null
    ): void {
        RequestLog::create([
            'mock_server_id' => $server->id,
            'mock_endpoint_id' => $endpoint?->id,
            'mock_credential_id' => $matchedCredential?->id,
            'method' => $request->method(),
            'path' => $path ?? '/',
            'headers' => $this->sanitizeHeaders($request->headers->all()),
            'body' => $request->all() ?: null,
            'query_params' => $request->query() ?: null,
            'ip' => $request->ip(),
            'auth_status' => $authStatus,
            'response_status' => $responseStatus,
        ]);
    }

    private function resolveServer(string $slug): ?MockServer
    {
        return $this->cacheRemember(
            "mock:server:{$slug}",
            self::SERVER_CACHE_TTL,
            fn () => MockServer::where('slug', $slug)
                ->where('is_active', true)
                ->with(['endpoints', 'credentials' => fn ($q) => $q->where('is_active', true)])
                ->first()
        );
    }

    private function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];
        foreach ($headers as $key => $values) {
            $sanitized[$key] = is_array($values) ? implode(', ', $values) : $values;
        }
        return $sanitized;
    }
}
