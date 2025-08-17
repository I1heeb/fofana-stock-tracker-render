<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite-compatible approach: recreate the table with new enum values
        if (DB::getDriverName() === 'sqlite') {
            // For SQLite, we need to recreate the table
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'packaging_agent', 'inventory_manager', 'service_client'])
                      ->default('packaging_agent')
                      ->after('email');
            });
        } else {
            // For PostgreSQL and other databases
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'packaging_agent', 'inventory_manager', 'service_client'])
                      ->default('packaging_agent')
                      ->after('email');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['packaging_team', 'service_client'])
                  ->default('packaging_team')
                  ->after('email');
        });
    }
};