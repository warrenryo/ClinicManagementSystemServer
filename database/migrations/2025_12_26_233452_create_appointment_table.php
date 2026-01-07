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
        Schema::create('appointment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_details_id');
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');

            $table->string('reason')->nullable();
            $table->text('notes')->nullable();

            $table->integer('status')->default(0);
            $table->integer('type')->default(0);

            $table->string('qr_token')->unique()->nullable();
            $table->foreign('user_details_id')->references('id')->on('user_details')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment');
    }
};
