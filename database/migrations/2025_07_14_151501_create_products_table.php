<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
        $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->integer('low_stock_threshold')->default(10);
        
            $table->timestamps();
                
                $table->index(['stock_quantity', 'low_stock_threshold']);
     });
 }
    }
    
       public function down(): void
    {
        Schema::dropIfExists('products');
    }
};



