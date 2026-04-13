<?php

namespace App\Http\Controllers;

use App\Services\MockHandler\MockHandlerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MockHandlerController extends Controller
{
    public function __construct(
        private readonly MockHandlerService $service
    ) {}

    public function handle(Request $request, string $slug, ?string $path = null): JsonResponse
    {
        return $this->service->handleRequest($request, $slug, $path);
    }
}
