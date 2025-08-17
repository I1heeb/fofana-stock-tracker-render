<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseHealthMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            
            // Test a simple query
            DB::select('SELECT 1');
            
        } catch (\Exception $e) {
            Log::error('Database connection failed', [
                'error' => $e->getMessage(),
                'url' => $request->url(),
            ]);
            
            return response()->json([
                'error' => 'Database connection failed',
                'message' => 'Please try again later'
            ], 503);
        }
        
        return $next($request);
    }
}
