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
        Schema::table('appointment', function (Blueprint $table) {
            $table->dropForeign(['assigned_doctor_id']);
            $table->dropColumn('assigned_doctor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_doctor_id')->nullable()->after('status');
            $table->foreign('assigned_doctor_id')->references('id')->on('user_details')->onDelete('cascade');
        });
    }
};
