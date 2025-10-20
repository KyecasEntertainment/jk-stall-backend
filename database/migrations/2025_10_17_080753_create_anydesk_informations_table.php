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
        Schema::create('anydesk_informations', function (Blueprint $table) {
            $table->id();
            $table->string("anydesk_id")->index();
            $table->string("pc_info");
            $table->timestamps();
        });
    }

    /**
     * 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anydesk_informations');
    }
};
