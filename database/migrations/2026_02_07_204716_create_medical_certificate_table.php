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
        Schema::create('medical_certificate', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medical_records_id');
            $table->date('date_issued')->nullable();
            $table->string('diagnosis')->nullable();
            $table->string('chief_complaint')->nullable();
            $table->text('physical_examination')->nullable();
            $table->text('recommendations')->nullable();
            $table->date('rest_period_from')->nullable();
            $table->date('rest_period_to')->nullable();
            $table->integer('number_of_days')->nullable();
            $table->boolean('fit_to_work')->default(false);
            $table->boolean('needs_follow_up')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->text('restrictions')->nullable();
            $table->text('remarks')->nullable();
            $table->longText('doctor_signature')->nullable();
            $table->foreign('medical_records_id')->references('id')->on('medical_records')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_certificate');
    }
};
