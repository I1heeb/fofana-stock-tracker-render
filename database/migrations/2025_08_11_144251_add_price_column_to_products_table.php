<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add the missing price column
            if (!Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('description');
            }

            // Rename minimum_stock_level to minimum_stock if needed
            if (Schema::hasColumn('products', 'minimum_stock_level') && !Schema::hasColumn('products', 'minimum_stock')) {
                $table->renameColumn('minimum_stock_level', 'minimum_stock');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Remove the price column
            if (Schema::hasColumn('products', 'price')) {
                $table->dropColumn('price');
            }

            // Rename back to minimum_stock_level if needed
            if (Schema::hasColumn('products', 'minimum_stock') && !Schema::hasColumn('products', 'minimum_stock_level')) {
                $table->renameColumn('minimum_stock', 'minimum_stock_level');
            }
        });
    }
};
