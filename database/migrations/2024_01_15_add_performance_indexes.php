<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if index exists before creating
        $this->createIndexIfNotExists('orders', 'orders_status_created_idx', ['status', 'created_at']);
        $this->createIndexIfNotExists('orders', 'orders_user_status_idx', ['user_id', 'status']);
        $this->createIndexIfNotExists('orders', 'orders_updated_idx', ['updated_at']);

        // Order Items - heavy joins
        $this->createIndexIfNotExists('order_items', 'order_items_composite_idx', ['order_id', 'product_id']);
        $this->createIndexIfNotExists('order_items', 'order_items_product_idx', ['product_id']);

        // Products - stock queries (only create indexes for existing columns)
        $this->createIndexIfNotExists('products', 'products_stock_threshold_idx', ['stock_quantity', 'low_stock_threshold']);
        
        // Only create minimum_stock index if column exists
        if (Schema::hasColumn('products', 'minimum_stock')) {
            $this->createIndexIfNotExists('products', 'products_stock_minimum_idx', ['stock_quantity', 'minimum_stock']);
        }
        
        $this->createIndexIfNotExists('products', 'products_updated_idx', ['updated_at']);
        $this->createIndexIfNotExists('products', 'products_sku_idx', ['sku']);

        // Logs - audit queries
        $this->createIndexIfNotExists('logs', 'logs_user_date_idx', ['user_id', 'created_at']);
        $this->createIndexIfNotExists('logs', 'logs_action_date_idx', ['action', 'created_at']);
        $this->createIndexIfNotExists('logs', 'logs_order_idx', ['order_id']);
    }

    public function down()
    {
        $this->dropIndexIfExists('orders', 'orders_status_created_idx');
        $this->dropIndexIfExists('orders', 'orders_user_status_idx');
        $this->dropIndexIfExists('orders', 'orders_updated_idx');

        $this->dropIndexIfExists('order_items', 'order_items_composite_idx');
        $this->dropIndexIfExists('order_items', 'order_items_product_idx');

        $this->dropIndexIfExists('products', 'products_stock_threshold_idx');
        $this->dropIndexIfExists('products', 'products_stock_minimum_idx');
        $this->dropIndexIfExists('products', 'products_updated_idx');
        $this->dropIndexIfExists('products', 'products_sku_idx');

        $this->dropIndexIfExists('logs', 'logs_user_date_idx');
        $this->dropIndexIfExists('logs', 'logs_action_date_idx');
        $this->dropIndexIfExists('logs', 'logs_order_idx');
    }

    private function createIndexIfNotExists($table, $indexName, $columns)
    {
        // Check if all columns exist first
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return; // Skip if any column doesn't exist
            }
        }

        // Database-agnostic index existence check
        $exists = false;

        if (DB::getDriverName() === 'sqlite') {
            // For SQLite, check sqlite_master table
            $exists = DB::select("SELECT 1 FROM sqlite_master WHERE type = 'index' AND name = ?", [$indexName]);
        } elseif (DB::getDriverName() === 'pgsql') {
            // For PostgreSQL
            $exists = DB::select("SELECT 1 FROM pg_indexes WHERE indexname = ?", [$indexName]);
        } else {
            // For MySQL and others, try to create anyway (Laravel will handle duplicates)
            $exists = false;
        }

        if (empty($exists)) {
            try {
                Schema::table($table, function (Blueprint $table) use ($indexName, $columns) {
                    $table->index($columns, $indexName);
                });
            } catch (\Exception $e) {
                // Index might already exist, ignore the error
            }
        }
    }

    private function dropIndexIfExists($table, $indexName)
    {
        $exists = false;

        if (DB::getDriverName() === 'sqlite') {
            // For SQLite, check sqlite_master table
            $exists = DB::select("SELECT 1 FROM sqlite_master WHERE type = 'index' AND name = ?", [$indexName]);
        } elseif (DB::getDriverName() === 'pgsql') {
            // For PostgreSQL
            $exists = DB::select("SELECT 1 FROM pg_indexes WHERE indexname = ?", [$indexName]);
        } else {
            // For MySQL and others, try to drop anyway
            $exists = true;
        }

        if (!empty($exists)) {
            try {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            } catch (\Exception $e) {
                // Index might not exist, ignore the error
            }
        }
    }
};



