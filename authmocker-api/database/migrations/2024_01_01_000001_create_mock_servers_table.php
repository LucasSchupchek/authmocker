<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_servers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('auth_type');
            $table->json('config');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('slug');
            $table->index('auth_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_servers');
    }
};
