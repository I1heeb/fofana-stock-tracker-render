<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only run for non-SQLite databases (SQLite enum already handles constraints)
        if (DB::getDriverName() !== 'sqlite') {
            // Drop the existing constraint
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");

            // Add the new constraint with admin role
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'packaging', 'service_client'))");
        }
    }

    public function down(): void
    {
        // Only run for non-SQLite databases
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('packaging', 'service_client'))");
        }
    }
};