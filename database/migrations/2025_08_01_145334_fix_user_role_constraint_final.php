<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run for non-SQLite databases (SQLite enum already handles constraints)
        if (DB::getDriverName() !== 'sqlite') {
            // Drop any existing role constraint
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');

            // Add the correct constraint with all valid roles (including packaging_agent and super_admin)
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'packaging', 'packaging_agent', 'service_client', 'super_admin'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run for non-SQLite databases
        if (DB::getDriverName() !== 'sqlite') {
            // Drop the constraint
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        }
    }
};
