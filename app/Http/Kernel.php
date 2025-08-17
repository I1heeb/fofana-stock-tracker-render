protected $routeMiddleware = [
    // ... existing middleware
    'cache.response' => \App\Http\Middleware\CacheResponse::class,
];
