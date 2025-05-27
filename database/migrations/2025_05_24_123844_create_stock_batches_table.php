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
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->string('batch_id');
            $table->string('product_id');
            $table->foreign('product_id')->references('product_id')->on('products_lists')->onDelete('cascade');
            $table->string('product_name');
            $table->integer('entry_quantity');
            $table->decimal('unit_cost', 8, 2);
            $table->timestamp('received_at');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
