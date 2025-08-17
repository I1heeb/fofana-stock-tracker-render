<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestConnection extends Command
{
    protected $signature = 'test:connection';
    protected $description = 'Test Supabase connection via HTTP';

    public function handle()
    {
        $this->info('🔍 Testing Supabase HTTP Connection...');
        
        try {
            $response = Http::withHeaders([
                'apikey' => config('services.supabase.anon_key'),
                'Authorization' => 'Bearer ' . config('services.supabase.anon_key'),
            ])->get(config('services.supabase.url') . '/rest/v1/');
            
            if ($response->successful()) {
                $this->info('✅ Supabase HTTP connection successful!');
                $this->info('Response: ' . $response->body());
            } else {
                $this->error('❌ HTTP connection failed: ' . $response->status());
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Connection test failed: ' . $e->getMessage());
        }
    }
}