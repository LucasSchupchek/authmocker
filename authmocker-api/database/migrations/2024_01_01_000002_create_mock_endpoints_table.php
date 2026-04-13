<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_endpoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mock_server_id');
            $table->string('method')->default('GET');
            $table->string('path');
            $table->integer('response_status')->default(200);
            $table->json('response_body')->nullable();
            $table->json('response_headers')->nullable();
            $table->integer('delay_ms')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('mock_server_id')
                ->references('id')
                ->on('mock_servers')
                ->onDelete('cascade');

            $table->index(['mock_server_id', 'method', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_endpoints');
    }
};
