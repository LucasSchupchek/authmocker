<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMockEndpointRequest;
use App\Http\Requests\UpdateMockEndpointRequest;
use App\Http\Resources\MockEndpointResource;
use App\Services\MockEndpointService;
use App\Services\MockServerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MockEndpointController extends Controller
{
    public function __construct(
        private readonly MockEndpointService $endpointService,
        private readonly MockServerService $serverService
    ) {}

    public function index(Request $request, string $serverId): JsonResponse
    {
        $server = $this->serverService->findByIdAndUser($serverId, $request->user_id);
        $endpoints = $this->endpointService->listByServer($server);

        return MockEndpointResource::collection($endpoints)->response();
    }

    public function store(StoreMockEndpointRequest $request, string $serverId): JsonResponse
    {
        $server = $this->serverService->findByIdAndUser($serverId, $request->user_id);
        $endpoint = $this->endpointService->create($server, $request->validated());

        return (new MockEndpointResource($endpoint))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $endpoint = $this->endpointService->findById($id);
        $this->serverService->findByIdAndUser($endpoint->mock_server_id, $request->user_id);

        return (new MockEndpointResource($endpoint))->response();
    }

    public function update(UpdateMockEndpointRequest $request, string $id): JsonResponse
    {
        $endpoint = $this->endpointService->findById($id);
        $this->serverService->findByIdAndUser($endpoint->mock_server_id, $request->user_id);
        $endpoint = $this->endpointService->update($endpoint, $request->validated());

        return (new MockEndpointResource($endpoint))->response();
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $endpoint = $this->endpointService->findById($id);
        $this->serverService->findByIdAndUser($endpoint->mock_server_id, $request->user_id);
        $this->endpointService->delete($endpoint);

        return response()->json(null, 204);
    }
}
