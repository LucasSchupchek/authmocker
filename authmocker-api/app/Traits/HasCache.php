<?php

namespace App\Traits;

use Closure;
use Illuminate\Support\Facades\Cache;

trait HasCache
{
    protected function cacheRemember(string $key, int $ttlSeconds, Closure $callback): mixed
    {
        return Cache::remember($key, $ttlSeconds, $callback);
    }

    protected function cacheForget(string ...$keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    protected function cacheForgetByPrefix(string $prefix): void
    {
        $redis = Cache::getStore()->getRedis();
        $cachePrefix = config('cache.prefix') . ':';

        $cursor = null;
        do {
            [$cursor, $keys] = $redis->scan($cursor ?: 0, [
                'match' => $cachePrefix . $prefix . '*',
                'count' => 100,
            ]);

            if (!empty($keys)) {
                $redis->del(...$keys);
            }
        } while ($cursor);
    }
}
