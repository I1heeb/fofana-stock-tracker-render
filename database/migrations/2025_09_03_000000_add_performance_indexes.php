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
        // Add indexes safely (skip if already exists)
        try {
            Schema::table('users', function (Blueprint $table) {
                // Add indexes for common queries
                if (!$this->indexExists('users', 'users_email_index')) {
                    $table->index('email');
                }
                if (!$this->indexExists('users', 'users_role_index')) {
                    $table->index('role');
                }
                if (!$this->indexExists('users', 'users_role_is_super_admin_index')) {
                    $table->index(['role', 'is_super_admin']);
                }
            });
        } catch (\Exception $e) {
            // Skip if table doesn't exist or indexes already exist
        }

        try {
            Schema::table('products', function (Blueprint $table) {
                // Add indexes for product searches (skip if exists)
                if (!$this->indexExists('products', 'products_name_index')) {
                    $table->index('name');
                }
                if (!$this->indexExists('products', 'products_barcode_index')) {
                    $table->index('barcode');
                }
                if (!$this->indexExists('products', 'products_supplier_id_index')) {
                    $table->index('supplier_id');
                }
                if (!$this->indexExists('products', 'products_supplier_id_name_index')) {
                    $table->index(['supplier_id', 'name']);
                }
                if (!$this->indexExists('products', 'products_stock_quantity_index')) {
                    $table->index('stock_quantity');
                }
                if (!$this->indexExists('products', 'products_stock_quantity_low_stock_threshold_index')) {
                    $table->index(['stock_quantity', 'low_stock_threshold']);
                }
            });
        } catch (\Exception $e) {
            // Skip if table doesn't exist or indexes already exist
        }

        // Add other indexes safely
        $this->addIndexSafely('orders', 'status');
        $this->addIndexSafely('orders', 'created_at');
        $this->addIndexSafely('orders', ['status', 'created_at']);
        $this->addIndexSafely('orders', 'total_amount');

        $this->addIndexSafely('order_items', 'order_id');
        $this->addIndexSafely('order_items', 'product_id');
        $this->addIndexSafely('order_items', ['order_id', 'product_id']);

        if (Schema::hasTable('stock_histories')) {
            $this->addIndexSafely('stock_histories', 'product_id');
            $this->addIndexSafely('stock_histories', 'user_id');
            $this->addIndexSafely('stock_histories', 'created_at');
            $this->addIndexSafely('stock_histories', ['product_id', 'created_at']);
        }

        if (Schema::hasTable('logs')) {
            $this->addIndexSafely('logs', 'user_id');
            $this->addIndexSafely('logs', 'action');
            $this->addIndexSafely('logs', 'created_at');
            $this->addIndexSafely('logs', ['user_id', 'created_at']);
        }
    }

    /**
     * Helper method to check if index exists
     */
    private function indexExists($table, $indexName)
    {
        try {
            $indexes = \DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $indexName]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Helper method to add index safely
     */
    private function addIndexSafely($table, $columns)
    {
        try {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    $table->index($columns);
                });
            }
        } catch (\Exception $e) {
            // Index already exists or other error - skip
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes safely (ignore errors if they don't exist)
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['email']);
                $table->dropIndex(['role']);
                $table->dropIndex(['role', 'is_super_admin']);
            });
        } catch (\Exception $e) {
            // Ignore if indexes don't exist
        }

        try {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['name']);
                $table->dropIndex(['barcode']);
                $table->dropIndex(['supplier_id']);
                $table->dropIndex(['supplier_id', 'name']);
                $table->dropIndex(['stock_quantity']);
                $table->dropIndex(['stock_quantity', 'low_stock_threshold']);
            });
        } catch (\Exception $e) {
            // Ignore if indexes don't exist
        }

        // Drop other indexes safely
        $this->dropIndexSafely('orders', ['status', 'created_at', 'total_amount']);
        $this->dropIndexSafely('order_items', ['order_id', 'product_id']);
        $this->dropIndexSafely('stock_histories', ['product_id', 'user_id', 'created_at']);
        $this->dropIndexSafely('logs', ['user_id', 'action', 'created_at']);
    }

    /**
     * Helper method to drop indexes safely
     */
    private function dropIndexSafely($table, $columns)
    {
        try {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    foreach ($columns as $column) {
                        $table->dropIndex([$column]);
                    }
                });
            }
        } catch (\Exception $e) {
            // Ignore if indexes don't exist
        }
    }
};
