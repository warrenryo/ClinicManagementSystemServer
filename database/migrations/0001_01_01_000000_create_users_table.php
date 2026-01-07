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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('role');
            $table->json('user_access')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('access_token')->default('');
            $table->text('refresh_token')->default('');
            $table->timestamp('refresh_token_createdAt')->nullable();
            $table->timestamp('refresh_token_expiresAt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
