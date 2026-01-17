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
            $table->renameColumn('amount', 'temperature');
            $table->decimal('height', 8, 2)->nullable()->change();
            $table->decimal('weight', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->renameColumn('temperature', 'amount');
            $table->decimal('height')->change();
            $table->decimal('weight')->change();
        });
    }
};
