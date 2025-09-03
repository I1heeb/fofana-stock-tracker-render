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
            // Supprimer l'ancienne contrainte
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');

            // Ajouter la nouvelle contrainte avec tous les rôles valides
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
            // Supprimer la contrainte
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');

            // Remettre l'ancienne contrainte (si nécessaire)
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'packaging', 'packaging_agent', 'service_client', 'super_admin'))");
        }
    }
};
