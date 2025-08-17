<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, we need to recreate the role column with correct enum values
        if (DB::getDriverName() === 'sqlite') {
            // Drop the role column
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });

            // Add it back with correct values
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'packaging_agent', 'service_client'])
                      ->default('packaging_agent')
                      ->after('email');
            });
        } else {
            // For PostgreSQL and other databases
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'packaging_agent', 'service_client'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
            
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'packaging', 'service_client'])
                      ->default('packaging')
                      ->after('email');
            });
        } else {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'packaging', 'service_client'))");
        }
    }
};

