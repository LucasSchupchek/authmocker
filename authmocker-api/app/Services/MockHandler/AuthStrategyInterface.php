<?php

namespace App\Services\MockHandler;

use App\Models\MockCredential;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface AuthStrategyInterface
{
    public function validate(Request $request, array $config, Collection $credentials): ?MockCredential;

    public function getTokenEndpointResponse(Request $request, array $config, Collection $credentials): array;

    public function getErrorResponse(): array;
}
