<?php

namespace App\Http\Controllers;

use App\Http\Resources\RequestLogResource;
use App\Services\MockServerService;
use App\Services\RequestLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestLogController extends Controller
{
    public function __construct(
        private readonly RequestLogService $logService,
        private readonly MockServerService $serverService
    ) {}

    public function index(Request $request, string $serverId): JsonResponse
    {
        $server = $this->serverService->findByIdAndUser($serverId, $request->user_id);
        $perPage = min((int) $request->query('per_page', 50), 100);
        $logs = $this->logService->listByServer($server, $perPage);

        return RequestLogResource::collection($logs)->response();
    }

    public function destroy(Request $request, string $serverId): JsonResponse
    {
        $server = $this->serverService->findByIdAndUser($serverId, $request->user_id);
        $count = $this->logService->clearByServer($server);

        return response()->json(['message' => "{$count} logs cleared."]);
    }
}
