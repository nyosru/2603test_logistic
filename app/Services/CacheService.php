<?php

namespace App\Services;

use Closure;
use Illuminate\Contracts\Cache\LockProvider as LockProviderContract;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    private object $missingValue;

    public function __construct()
    {
        $this->missingValue = new \stdClass();
    }

    public function save(string $key, mixed $data, int $ttlSeconds): bool
    {
        if ($ttlSeconds <= 0) {
            return false;
        }

        return Cache::put($key, $data, $ttlSeconds);
    }

    /**
     * Returns cached data or false if key is not found.
     * If resolver is passed, protects cache rebuild from stampede via lock.
     */
    public function get(
        string $key,
        ?Closure $resolver = null,
        int $ttlSeconds = 60,
        int $lockSeconds = 10,
        int $waitAttempts = 5,
        int $waitMs = 100
    ): mixed {
        $cached = Cache::get($key, $this->missingValue);
        if ($cached !== $this->missingValue) {
            return $cached;
        }

        if ($resolver === null) {
            return false;
        }

        $store = Cache::getStore();
        if (! $store instanceof LockProviderContract) {
            $fresh = $resolver();
            $this->save($key, $fresh, $ttlSeconds);

            return $fresh;
        }

        $lock = Cache::lock("cache:stampede:{$key}", $lockSeconds);

        if ($lock->get()) {
            try {
                $cached = Cache::get($key, $this->missingValue);
                if ($cached !== $this->missingValue) {
                    return $cached;
                }

                $fresh = $resolver();
                $this->save($key, $fresh, $ttlSeconds);

                return $fresh;
            } finally {
                $lock->release();
            }
        }

        // Another process builds value now, wait briefly and retry cache reads.
        for ($i = 0; $i < max(1, $waitAttempts); $i++) {
            usleep(max(1, $waitMs) * 1000);

            $cached = Cache::get($key, $this->missingValue);
            if ($cached !== $this->missingValue) {
                return $cached;
            }
        }

        // Fallback: lock holder may fail, so recompute to avoid returning empty forever.
        $fresh = $resolver();
        $this->save($key, $fresh, $ttlSeconds);

        return $fresh;
    }
}
