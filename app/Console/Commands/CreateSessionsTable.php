<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Command
{
    protected $signature = 'sessions:create-table';
    protected $description = 'Create sessions table manually';

    public function handle()
    {
        try {
            if (Schema::hasTable('sessions')) {
                $this->info('Sessions table already exists');
                return;
            }

            DB::statement('
                CREATE TABLE sessions (
                    id VARCHAR(255) PRIMARY KEY,
                    user_id BIGINT NULL,
                    ip_address VARCHAR(45) NULL,
                    user_agent TEXT NULL,
                    payload TEXT NOT NULL,
                    last_activity INTEGER NOT NULL
                )
            ');

            DB::statement('CREATE INDEX sessions_user_id_index ON sessions (user_id)');
            DB::statement('CREATE INDEX sessions_last_activity_index ON sessions (last_activity)');

            $this->info('Sessions table created successfully');
        } catch (\Exception $e) {
            $this->error('Failed to create sessions table: ' . $e->getMessage());
        }
    }
}