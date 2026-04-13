<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mock_server_id');
            $table->uuid('mock_endpoint_id')->nullable();
            $table->string('method');
            $table->string('path');
            $table->json('headers')->nullable();
            $table->json('body')->nullable();
            $table->json('query_params')->nullable();
            $table->string('ip')->nullable();
            $table->string('auth_status')->default('skipped');
            $table->integer('response_status');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('mock_server_id')
                ->references('id')
                ->on('mock_servers')
                ->onDelete('cascade');

            $table->foreign('mock_endpoint_id')
                ->references('id')
                ->on('mock_endpoints')
                ->onDelete('set null');

            $table->index(['mock_server_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
