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
        Schema::create('ai_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('ai_id')->unique(); // e.g., ai-summary-001
            $table->string('title');
            $table->text('summary');
            $table->json('insights'); // store insights as JSON array
            $table->integer('confidence')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_summary');
    }
};
