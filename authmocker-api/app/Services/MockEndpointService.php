<?php

namespace App\Services;

use App\Models\MockEndpoint;
use App\Models\MockServer;
use App\Traits\HasCache;
use Illuminate\Database\Eloquent\Collection;

class MockEndpointService
{
    use HasCache;

    private const CACHE_TTL = 600; // 10 minutes

    public function listByServer(MockServer $server): Collection
    {
        return $this->cacheRemember(
            "endpoints:server:{$server->id}",
            self::CACHE_TTL,
            fn () => $server->endpoints()
                ->orderBy('path')
                ->orderBy('method')
                ->get()
        );
    }

    public function findById(string $id): MockEndpoint
    {
        return $this->cacheRemember(
            "endpoint:{$id}",
            self::CACHE_TTL,
            fn () => MockEndpoint::with('mockServer')->findOrFail($id)
        );
    }

    public function create(MockServer $server, array $data): MockEndpoint
    {
        $data['path'] = ltrim($data['path'], '/');
        $endpoint = $server->endpoints()->create($data);

        $this->invalidateEndpointCache($server);

        return $endpoint;
    }

    public function update(MockEndpoint $endpoint, array $data): MockEndpoint
    {
        if (isset($data['path'])) {
            $data['path'] = ltrim($data['path'], '/');
        }

        $endpoint->update($data);
        $endpoint = $endpoint->fresh();

        $this->invalidateEndpointCache($endpoint->mockServer, $endpoint->id);

        return $endpoint;
    }

    public function delete(MockEndpoint $endpoint): void
    {
        $server = $endpoint->mockServer;
        $endpointId = $endpoint->id;

        $endpoint->delete();

        $this->invalidateEndpointCache($server, $endpointId);
    }

    private function invalidateEndpointCache(MockServer $server, ?string $endpointId = null): void
    {
        $keys = [
            "endpoints:server:{$server->id}",
            "mock:server:{$server->slug}",
        ];

        if ($endpointId) {
            $keys[] = "endpoint:{$endpointId}";
        }

        $this->cacheForget(...$keys);
    }
}
