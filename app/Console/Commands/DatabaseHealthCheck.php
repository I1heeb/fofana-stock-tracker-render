<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseHealthCheck extends Command
{
    protected $signature = 'db:health';
    protected $description = 'Check database connection and Supabase integration';

    public function handle()
    {
        $this->info('🔍 Checking Supabase Database Connection...');
        
        try {
            // Test pooled connection
            $this->info('Testing pooled connection (port 6543)...');
            $pdo = DB::connection('pgsql')->getPdo();
            $this->info('✅ Pooled connection successful');
            
            // Test direct connection
            $this->info('Testing direct connection (port 5432)...');
            $directPdo = DB::connection('pgsql_direct')->getPdo();
            $this->info('✅ Direct connection successful');
            
            // Get database info
            $version = DB::select('SELECT version() as version')[0]->version;
            $this->info("📊 PostgreSQL Version: {$version}");
            
            // Test table creation
            $this->info('Testing table operations...');
            DB::statement('CREATE TABLE IF NOT EXISTS health_check (id SERIAL PRIMARY KEY, created_at TIMESTAMP DEFAULT NOW())');
            DB::table('health_check')->insert(['created_at' => now()]);
            $count = DB::table('health_check')->count();
            DB::statement('DROP TABLE health_check');
            $this->info("✅ Table operations successful (inserted {$count} records)");
            
            $this->info('🎉 All database checks passed!');
            
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}