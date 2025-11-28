<?php

namespace App\Cache;

class Cache
{
    protected static string $cacheDir = __DIR__ . '/storage/';

    // Store data in cache
    public static function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        $cacheFile = self::getCacheFilePath($key);
        $data = [
            'expires_at' => time() + $ttl,
            'value' => $value
        ];
        return file_put_contents($cacheFile, serialize($data)) !== false;
    }

    // Get data from cache
    public static function get(string $key): mixed
    {
        $cacheFile = self::getCacheFilePath($key);
        if (!file_exists($cacheFile)) {
            return null;
        }

        $data = unserialize(file_get_contents($cacheFile));

        // Expire check
        if ($data['expires_at'] < time()) {
            unlink($cacheFile);
            return null;
        }

        return $data['value'];
    }

    // Delete cache by key
    public static function delete(string $key): bool
    {
        $cacheFile = self::getCacheFilePath($key);
        return file_exists($cacheFile) ? unlink($cacheFile) : false;
    }

    // Clear all cache
    public static function clear(): void
    {
        array_map('unlink', glob(self::$cacheDir . '*'));
    }

    // Generate cache file path
    private static function getCacheFilePath(string $key): string
    {
        return self::$cacheDir . md5($key) . '.cache';
    }
}
