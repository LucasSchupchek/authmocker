<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMockServerRequest;
use App\Http\Requests\UpdateMockServerRequest;
use App\Http\Resources\MockServerResource;
use App\Services\MockServerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MockServerController extends Controller
{
    public function __construct(
        private readonly MockServerService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $servers = $this->service->listByUser($request->user_id);

        return MockServerResource::collection($servers)->response();
    }

    public function store(StoreMockServerRequest $request): JsonResponse
    {
        $server = $this->service->create($request->validated(), $request->user_id);

        return (new MockServerResource($server))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $server = $this->service->findByIdAndUser($id, $request->user_id);

        return (new MockServerResource($server))->response();
    }

    public function update(UpdateMockServerRequest $request, string $id): JsonResponse
    {
        $server = $this->service->findByIdAndUser($id, $request->user_id);
        $server = $this->service->update($server, $request->validated());

        return (new MockServerResource($server))->response();
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $server = $this->service->findByIdAndUser($id, $request->user_id);
        $this->service->delete($server);

        return response()->json(null, 204);
    }
}
