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
        Schema::create('medical_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medical_records_id');
            $table->unsignedBigInteger('products_id');
            $table->integer('quantity');
            $table->foreign('medical_records_id')->references('id')->on('medical_records')->onDelete('cascade');
            $table->foreign('products_id')->references('id')->on('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_items');
    }
};
