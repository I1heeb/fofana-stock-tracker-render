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
        Schema::table('users', function (Blueprint $table) {
            // Add indexes for common queries
            $table->index('email');
            $table->index('role');
            $table->index(['role', 'is_super_admin']);
        });

        Schema::table('products', function (Blueprint $table) {
            // Add indexes for product searches
            $table->index('name');
            $table->index('barcode');
            $table->index('supplier_id');
            $table->index(['supplier_id', 'name']);
            $table->index('stock_quantity');
            $table->index(['stock_quantity', 'low_stock_threshold']);
        });

        Schema::table('orders', function (Blueprint $table) {
            // Add indexes for order queries
            $table->index('status');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index('total_amount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            // Add indexes for order item queries
            $table->index('order_id');
            $table->index('product_id');
            $table->index(['order_id', 'product_id']);
        });

        if (Schema::hasTable('stock_histories')) {
            Schema::table('stock_histories', function (Blueprint $table) {
                // Add indexes for stock history queries
                $table->index('product_id');
                $table->index('user_id');
                $table->index('created_at');
                $table->index(['product_id', 'created_at']);
            });
        }

        if (Schema::hasTable('logs')) {
            Schema::table('logs', function (Blueprint $table) {
                // Add indexes for log queries
                $table->index('user_id');
                $table->index('action');
                $table->index('created_at');
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['role']);
            $table->dropIndex(['role', 'is_super_admin']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['barcode']);
            $table->dropIndex(['supplier_id']);
            $table->dropIndex(['supplier_id', 'name']);
            $table->dropIndex(['stock_quantity']);
            $table->dropIndex(['stock_quantity', 'low_stock_threshold']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['total_amount']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['order_id', 'product_id']);
        });

        if (Schema::hasTable('stock_histories')) {
            Schema::table('stock_histories', function (Blueprint $table) {
                $table->dropIndex(['product_id']);
                $table->dropIndex(['user_id']);
                $table->dropIndex(['created_at']);
                $table->dropIndex(['product_id', 'created_at']);
            });
        }

        if (Schema::hasTable('logs')) {
            Schema::table('logs', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
                $table->dropIndex(['action']);
                $table->dropIndex(['created_at']);
                $table->dropIndex(['user_id', 'created_at']);
            });
        }
    }
};
