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
        Schema::create('total_product_quantities', function (Blueprint $table) {
            $table->id();
            $table->string("product_id")->index();
            $table->foreign("product_id")->references("product_id")->on("products_lists")->onDelete("cascade");
            $table->string("all_total_quantity")->nullable();
            $table->string("current_total_quantity")->nullable();
            $table->string("total_displayed_quantity")->nullable();
            $table->string("sold_quantity")->nullable();
            $table->string("total_discarded_quantity")->nullable();
            $table->string("notes")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('total_product_quantities');
    }
};
