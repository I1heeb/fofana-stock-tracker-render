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
        // Mettre à jour tous les produits qui n'ont pas de low_stock_threshold défini
        DB::table('products')
            ->whereNull('low_stock_threshold')
            ->orWhere('low_stock_threshold', 0)
            ->update(['low_stock_threshold' => 10]);

        // S'assurer que la colonne a une valeur par défaut
        Schema::table('products', function (Blueprint $table) {
            $table->integer('low_stock_threshold')->default(10)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pas de rollback nécessaire pour cette migration
    }
};
