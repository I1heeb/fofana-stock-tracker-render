<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only run for non-SQLite databases (SQLite enum already handles constraints)
        if (DB::getDriverName() !== 'sqlite') {
            // Supprimer l'ancienne contrainte
            DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");

            // Ajouter la nouvelle contrainte avec tous les statuts
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending', 'processing', 'packed', 'out', 'completed', 'cancelled', 'returned'))");
        }
    }

    public function down(): void
    {
        // Only run for non-SQLite databases
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending', 'processing', 'completed', 'cancelled'))");
        }
    }
};