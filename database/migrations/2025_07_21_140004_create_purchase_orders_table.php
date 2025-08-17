<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('po_number')->unique();
                $table->enum('status', ['draft', 'sent', 'confirmed', 'received', 'cancelled'])->default('draft');
                $table->date('order_date');
                $table->date('expected_date')->nullable();
                $table->date('received_date')->nullable();
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};

