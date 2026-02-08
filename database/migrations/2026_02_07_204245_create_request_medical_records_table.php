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
        Schema::create('request_medical_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_details_id');
            $table->unsignedBigInteger('medical_records_id');
            $table->boolean('is_done')->default(false);
            $table->foreign('user_details_id')->references('id')->on('user_details')->onDelete('cascade');
            $table->foreign('medical_records_id')->references('id')->on('medical_records')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_medical_records');
    }
};
