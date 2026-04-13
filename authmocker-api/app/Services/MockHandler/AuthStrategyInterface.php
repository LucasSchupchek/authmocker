<?php

namespace App\Services\MockHandler;

use Illuminate\Http\Request;

interface AuthStrategyInterface
{
    public function validate(Request $request, array $config): bool;

    public function getTokenEndpointResponse(Request $request, array $config): array;

    public function getErrorResponse(): array;
}
