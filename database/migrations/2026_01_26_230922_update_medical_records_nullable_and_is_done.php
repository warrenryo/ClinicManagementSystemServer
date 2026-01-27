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
            $table->string('symptoms')->nullable()->change();
            $table->string('remarks')->nullable()->change();

            $table->boolean('is_done')->default(false)->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->string('symptoms')->default('')->change();
            $table->string('remarks')->default('')->change();

            $table->dropColumn('is_done');
        });
    }
};
