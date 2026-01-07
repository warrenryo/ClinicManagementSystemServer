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
            $table->dropColumn(['start_time', 'end_time', 'notes']);
            $table->time('appointment_time')->after('appointment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment', function (Blueprint $table) {
            $table->time('start_time');
            $table->time('end_time');
            $table->text('notes')->nullable();
            $table->dropColumn('appointment_time');
        });
    }
};
