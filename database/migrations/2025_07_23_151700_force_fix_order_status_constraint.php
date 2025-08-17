<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only run for PostgreSQL databases (SQLite enum already handles constraints)
        if (DB::getDriverName() === 'pgsql') {
            // Forcer la suppression de toutes les contraintes sur status
            DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");

            // Ajouter la nouvelle contrainte avec TOUS les statuts
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status::text = ANY (ARRAY['pending'::character varying, 'processing'::character varying, 'packed'::character varying, 'out'::character varying, 'completed'::character varying, 'cancelled'::character varying, 'returned'::character varying]::text[]))");
        }
    }

    public function down(): void
    {
        // Only run for PostgreSQL databases
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
        }
    }
};