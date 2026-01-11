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
            $table->string('cancel_resched_reason')->nullable()->after('type');
            $table->string('reschedule_reason')->nullable()->after('cancel_resched_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment', function (Blueprint $table) {
            $table->dropColumn('cancel_resched_reason');
            $table->dropColumn('reschedule_reason');
        });
    }
};
