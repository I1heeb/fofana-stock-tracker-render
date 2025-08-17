<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support DROP CONSTRAINT, so we recreate the column
            // First drop any indexes that include the status column
            $indexesToDrop = [
                'orders_status_index',
                'orders_status_created_idx',
                'orders_user_status_idx',
                'orders_updated_idx'
            ];

            foreach ($indexesToDrop as $indexName) {
                try {
                    Schema::table('orders', function (Blueprint $table) use ($indexName) {
                        $table->dropIndex($indexName);
                    });
                } catch (\Exception $e) {
                    // Index might not exist, ignore
                }
            }

            // Also try dropping by column array
            try {
                Schema::table('orders', function (Blueprint $table) {
                    $table->dropIndex(['status']);
                });
            } catch (\Exception $e) {
                // Index might not exist, ignore
            }

            try {
                Schema::table('orders', function (Blueprint $table) {
                    $table->dropIndex(['status', 'created_at']);
                });
            } catch (\Exception $e) {
                // Index might not exist, ignore
            }

            try {
                Schema::table('orders', function (Blueprint $table) {
                    $table->dropIndex(['user_id', 'status']);
                });
            } catch (\Exception $e) {
                // Index might not exist, ignore
            }

            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('orders', function (Blueprint $table) {
                $table->enum('status', ['pending', 'processing', 'packed', 'out', 'completed', 'cancelled', 'returned'])
                      ->default('pending')
                      ->after('user_id');
            });
        } else {
            // For PostgreSQL and other databases
            DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending', 'processing', 'packed', 'out', 'completed', 'cancelled', 'returned'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support DROP CONSTRAINT, so we recreate the column
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('orders', function (Blueprint $table) {
                $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])
                      ->default('pending')
                      ->after('id');
            });
        } else {
            DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
            DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending', 'processing', 'completed', 'cancelled'))");
        }
    }
};