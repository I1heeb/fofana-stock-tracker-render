<?php

namespace App\Services;

class SupabaseService
{
    protected $client;

    public function __construct()
    {
        $this->client = app('supabase');
    }

    public function realTimeSubscribe(string $table, callable $callback): void
    {
        $this->client->realtime()
            ->channel('public:' . $table)
            ->on('*', $callback)
            ->subscribe();
    }

    public function uploadFile(string $bucket, string $path, $file): array
    {
        return $this->client->storage()
            ->from($bucket)
            ->upload($path, $file);
    }

    public function getPublicUrl(string $bucket, string $path): string
    {
        return $this->client->storage()
            ->from($bucket)
            ->getPublicUrl($path);
    }
}