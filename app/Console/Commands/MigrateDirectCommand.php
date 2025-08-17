<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateDirectCommand extends Command
{
    protected $signature = 'migrate:direct {--force}';
    protected $description = 'Run migrations using direct Supabase connection';

    public function handle()
    {
        $this->info('Running migrations with direct Supabase connection...');
        
        // Temporarily switch to direct connection
        config(['database.default' => 'pgsql_direct']);
        
        $exitCode = Artisan::call('migrate', [
            '--force' => $this->option('force')
        ]);
        
        // Switch back to pooled connection
        config(['database.default' => 'pgsql']);
        
        $this->info('Migrations completed!');
        return $exitCode;
    }
}