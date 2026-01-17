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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('user_details_id')->nullable();
            $table->decimal('amount', 8, 2)->default(36.5);
            $table->string('blood_pressure')->nullable();
            $table->integer('pulse_rate')->nullable();
            $table->decimal('height')->nullable();
            $table->decimal('weight')->nullable();
            //checkup_details
            $table->string('symptoms')->default('');
            $table->json('action_taken')->nullable();
            $table->string('remarks')->default('');
            $table->foreign('appointment_id')->references('id')->on('appointment')->onDelete('cascade');
            $table->foreign('user_details_id')->references('id')->on('user_details')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
