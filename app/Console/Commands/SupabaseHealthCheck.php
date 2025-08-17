<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SupabaseHealthCheck extends Command
{
    protected $signature = 'supabase:health';
    protected $description = 'Check Supabase client integration';

    public function handle()
    {
        $this->info('🔍 Checking Supabase Client Integration...');
        
        try {
            // Check configuration
            $url = config('services.supabase.url');
            $anonKey = config('services.supabase.anon_key');
            
            if (!$url || !$anonKey) {
                $this->error('❌ Supabase configuration missing');
                return 1;
            }
            
            $this->info("✅ Supabase URL: {$url}");
            $this->info("✅ Anon Key configured: " . substr($anonKey, 0, 20) . '...');
            
            // Test client creation
            if (app()->bound('supabase')) {
                $client = app('supabase');
                $this->info('✅ Supabase client created successfully');
            } else {
                $this->error('❌ Supabase client not bound');
                return 1;
            }
            
            $this->info('🎉 Supabase integration healthy!');
            
        } catch (\Exception $e) {
            $this->error('❌ Supabase check failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}