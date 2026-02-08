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
        Schema::table('medical_records', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->after('user_details_id')->nullable();
            $table->foreign('doctor_id')->references('id')->on('doctor_details')->onDelete('cascade');
            $table->string('reference_no')->after('doctor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropColumn('doctor_id');
            $table->dropColumn('reference_no');
        });
    }
};
