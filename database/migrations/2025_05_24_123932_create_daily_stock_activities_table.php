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
        Schema::create('daily_stock_activities', function (Blueprint $table) {
            $table->string("activity_id")->primary();
            $table->string("product_id")->index();
            $table->foreign("product_id")->references("product_id")->on("products_lists")->onDelete("cascade");
            $table->timestamp("date")->index();
            $table->string("displayed_quantity")->nullable();
            $table->string("back_quantity")->nullable();
            $table->string("notes")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_stock_activities');
    }

};
