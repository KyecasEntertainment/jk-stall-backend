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
            $table->string("batch_id")->primary();
            $table->string("product_id")->index();
            $table->string("product_name");
            $table->integer("remaining_quantity")->default(0);
            $table->decimal("unit_cost")->default(0.00);
            $table->timestamps("received_at");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
