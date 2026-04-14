<?php

namespace App\Services;

use App\Models\MockCredential;
use App\Models\MockServer;
use App\Traits\HasCache;
use Illuminate\Database\Eloquent\Collection;

class MockCredentialService
{
    use HasCache;

    private const CACHE_TTL = 600; // 10 minutes

    public function listByServer(MockServer $server): Collection
    {
        return $this->cacheRemember(
            "credentials:server:{$server->id}",
            self::CACHE_TTL,
            fn () => $server->credentials()
                ->orderBy('label')
                ->get()
        );
    }

    public function findById(string $id): MockCredential
    {
        return $this->cacheRemember(
            "credential:{$id}",
            self::CACHE_TTL,
            fn () => MockCredential::with('mockServer')->findOrFail($id)
        );
    }

    public function create(MockServer $server, array $data): MockCredential
    {
        $credential = $server->credentials()->create($data);

        $this->invalidateCredentialCache($server);

        return $credential;
    }

    public function update(MockCredential $credential, array $data): MockCredential
    {
        $credential->update($data);
        $credential = $credential->fresh();

        $this->invalidateCredentialCache($credential->mockServer, $credential->id);

        return $credential;
    }

    public function delete(MockCredential $credential): void
    {
        $server = $credential->mockServer;
        $credentialId = $credential->id;

        $credential->delete();

        $this->invalidateCredentialCache($server, $credentialId);
    }

    private function invalidateCredentialCache(MockServer $server, ?string $credentialId = null): void
    {
        $keys = [
            "credentials:server:{$server->id}",
            "mock:server:{$server->slug}",
            "server:{$server->id}:user:{$server->user_id}",
        ];

        if ($credentialId) {
            $keys[] = "credential:{$credentialId}";
        }

        $this->cacheForget(...$keys);
    }
}
