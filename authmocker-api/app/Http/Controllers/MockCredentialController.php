<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMockCredentialRequest;
use App\Http\Requests\UpdateMockCredentialRequest;
use App\Http\Resources\MockCredentialResource;
use App\Services\MockCredentialService;
use App\Services\MockServerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MockCredentialController extends Controller
{
    public function __construct(
        private readonly MockCredentialService $credentialService,
        private readonly MockServerService $serverService
    ) {}

    public function index(Request $request, string $serverId): JsonResponse
    {
        $server = $this->serverService->findByIdAndUser($serverId, $request->user_id);
        $credentials = $this->credentialService->listByServer($server);

        return MockCredentialResource::collection($credentials)->response();
    }

    public function store(StoreMockCredentialRequest $request, string $serverId): JsonResponse
    {
        $server = $this->serverService->findByIdAndUser($serverId, $request->user_id);
        $credential = $this->credentialService->create($server, $request->validated());

        return (new MockCredentialResource($credential))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $credential = $this->credentialService->findById($id);
        $this->serverService->findByIdAndUser($credential->mock_server_id, $request->user_id);

        return (new MockCredentialResource($credential))->response();
    }

    public function update(UpdateMockCredentialRequest $request, string $id): JsonResponse
    {
        $credential = $this->credentialService->findById($id);
        $this->serverService->findByIdAndUser($credential->mock_server_id, $request->user_id);
        $credential = $this->credentialService->update($credential, $request->validated());

        return (new MockCredentialResource($credential))->response();
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $credential = $this->credentialService->findById($id);
        $this->serverService->findByIdAndUser($credential->mock_server_id, $request->user_id);
        $this->credentialService->delete($credential);

        return response()->json(null, 204);
    }
}
