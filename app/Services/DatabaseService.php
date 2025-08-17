<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseService
{
    public static function reconnect(): bool
    {
        try {
            // Disconnect all connections
            DB::disconnect();
            
            // Reconnect
            DB::reconnect();
            
            // Test connection
            DB::select('SELECT 1');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Database reconnection failed', [
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    public static function isHealthy(): bool
    {
        try {
            DB::select('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}