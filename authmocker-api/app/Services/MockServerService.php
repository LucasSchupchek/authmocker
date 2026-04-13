<?php

namespace App\Services;

use App\Enums\AuthType;
use App\Models\MockServer;
use App\Traits\HasCache;
use Illuminate\Database\Eloquent\Collection;

class MockServerService
{
    use HasCache;

    private const CACHE_TTL = 600; // 10 minutes

    public function listByUser(string $userId): Collection
    {
        return $this->cacheRemember(
            "servers:user:{$userId}",
            self::CACHE_TTL,
            fn () => MockServer::where('user_id', $userId)
                ->withCount('endpoints')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function findByIdAndUser(string $id, string $userId): MockServer
    {
        return $this->cacheRemember(
            "server:{$id}:user:{$userId}",
            self::CACHE_TTL,
            fn () => MockServer::where('id', $id)
                ->where('user_id', $userId)
                ->with('endpoints')
                ->firstOrFail()
        );
    }

    public function create(array $data, string $userId): MockServer
    {
        $authType = AuthType::from($data['auth_type']);

        $server = MockServer::create([
            'user_id' => $userId,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'auth_type' => $authType,
            'config' => $data['config'] ?? $authType->defaultConfig(),
            'is_active' => $data['is_active'] ?? true,
            'description' => $data['description'] ?? null,
        ]);

        $this->invalidateUserCache($userId);

        return $server;
    }

    public function update(MockServer $server, array $data): MockServer
    {
        $oldSlug = $server->slug;

        if (isset($data['auth_type'])) {
            $data['auth_type'] = AuthType::from($data['auth_type']);
        }

        $server->update($data);
        $server = $server->fresh();

        $this->invalidateServerCache($server, $oldSlug);

        return $server;
    }

    public function delete(MockServer $server): void
    {
        $userId = $server->user_id;
        $slug = $server->slug;
        $serverId = $server->id;

        $server->delete();

        $this->invalidateServerCache($server, $slug);
        $this->cacheForget("endpoints:server:{$serverId}");
    }

    private function invalidateUserCache(string $userId): void
    {
        $this->cacheForget("servers:user:{$userId}");
    }

    private function invalidateServerCache(MockServer $server, ?string $oldSlug = null): void
    {
        $this->cacheForget(
            "servers:user:{$server->user_id}",
            "server:{$server->id}:user:{$server->user_id}",
            "mock:server:{$server->slug}",
        );

        if ($oldSlug && $oldSlug !== $server->slug) {
            $this->cacheForget("mock:server:{$oldSlug}");
        }
    }
}
