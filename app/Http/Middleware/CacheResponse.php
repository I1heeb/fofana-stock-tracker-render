<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheResponse
{
    public function handle(Request $request, Closure $next, $ttl = 60)
    {
        $key = 'api_cache:' . md5($request->fullUrl());
        
        if (Cache::has($key)) {
            return response()->json(Cache::get($key))
                           ->header('X-Cache', 'HIT');
        }

        $response = $next($request);
        
        if ($response->isSuccessful()) {
            Cache::put($key, $response->getData(), $ttl);
        }

        return $response->header('X-Cache', 'MISS');
    }
}