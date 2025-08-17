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
        // Add indexes and foreign keys to order_items table
        Schema::table('order_items', function (Blueprint $table) {
            // Add indexes for frequently queried columns (only if they don't exist)
            if (!$this->indexExists('order_items', 'order_items_order_id_index')) {
                $table->index('order_id');
            }
            if (!$this->indexExists('order_items', 'order_items_product_id_index')) {
                $table->index('product_id');
            }
            if (!$this->indexExists('order_items', 'order_items_order_id_product_id_index')) {
                $table->index(['order_id', 'product_id']);
            }
            
            // Foreign keys already exist, skip them
            // $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
        });

        // Add indexes to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!$this->indexExists('orders', 'orders_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('orders', 'orders_user_id_index')) {
                $table->index('user_id');
            }
            
            // Add foreign key constraint if it doesn't exist
            if (!$this->foreignKeyExists('orders', 'orders_user_id_foreign')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            }
        });

        // Add indexes to logs table
        Schema::table('logs', function (Blueprint $table) {
            if (!$this->indexExists('logs', 'logs_order_id_action_index')) {
                $table->index(['order_id', 'action']);
            }
            if (!$this->indexExists('logs', 'logs_product_id_action_index')) {
                $table->index(['product_id', 'action']);
            }
            
            // Add foreign key constraints if they don't exist
            if (!$this->foreignKeyExists('logs', 'logs_order_id_foreign')) {
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            }
            if (!$this->foreignKeyExists('logs', 'logs_product_id_foreign')) {
                $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            }
            if (!$this->foreignKeyExists('logs', 'logs_user_id_foreign')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign keys and indexes from order_items table
        Schema::table('order_items', function (Blueprint $table) {
            if ($this->foreignKeyExists('order_items', 'order_items_order_id_foreign')) {
                $table->dropForeign(['order_id']);
            }
            if ($this->foreignKeyExists('order_items', 'order_items_product_id_foreign')) {
                $table->dropForeign(['product_id']);
            }
            if ($this->indexExists('order_items', 'order_items_order_id_index')) {
                $table->dropIndex(['order_id']);
            }
            if ($this->indexExists('order_items', 'order_items_product_id_index')) {
                $table->dropIndex(['product_id']);
            }
            if ($this->indexExists('order_items', 'order_items_order_id_product_id_index')) {
                $table->dropIndex(['order_id', 'product_id']);
            }
        });

        // Remove indexes from orders table
        Schema::table('orders', function (Blueprint $table) {
            if ($this->foreignKeyExists('orders', 'orders_user_id_foreign')) {
                $table->dropForeign(['user_id']);
            }
            if ($this->indexExists('orders', 'orders_status_index')) {
                $table->dropIndex(['status']);
            }
            if ($this->indexExists('orders', 'orders_user_id_index')) {
                $table->dropIndex(['user_id']);
            }
        });

        // Remove indexes from logs table
        Schema::table('logs', function (Blueprint $table) {
            if ($this->foreignKeyExists('logs', 'logs_order_id_foreign')) {
                $table->dropForeign(['order_id']);
            }
            if ($this->foreignKeyExists('logs', 'logs_product_id_foreign')) {
                $table->dropForeign(['product_id']);
            }
            if ($this->foreignKeyExists('logs', 'logs_user_id_foreign')) {
                $table->dropForeign(['user_id']);
            }
            if ($this->indexExists('logs', 'logs_order_id_action_index')) {
                $table->dropIndex(['order_id', 'action']);
            }
            if ($this->indexExists('logs', 'logs_product_id_action_index')) {
                $table->dropIndex(['product_id', 'action']);
            }
        });
    }

    /**
     * Check if an index exists
     */
    private function indexExists($table, $index)
    {
        try {
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name = ?", [$index]);
                return !empty($result);
            } elseif ($driver === 'pgsql') {
                $result = DB::select("SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $index]);
                return !empty($result);
            } else {
                // For MySQL and other databases, just return false to skip index checks
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if a foreign key constraint exists
     */
    private function foreignKeyExists($table, $constraint)
    {
        try {
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                // SQLite doesn't have a reliable way to check foreign key constraints
                // Just return false to skip foreign key checks
                return false;
            } elseif ($driver === 'pgsql') {
                $result = DB::select("SELECT 1 FROM information_schema.table_constraints WHERE table_name = ? AND constraint_name = ?", [$table, $constraint]);
                return !empty($result);
            } else {
                // For MySQL and other databases, just return false to skip foreign key checks
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}; 
