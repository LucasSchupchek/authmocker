<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mock_server_id');
            $table->string('label');
            $table->boolean('is_active')->default(true);
            $table->json('credentials');
            $table->json('profile')->nullable();
            $table->timestamps();

            $table->foreign('mock_server_id')
                ->references('id')
                ->on('mock_servers')
                ->onDelete('cascade');

            $table->index('mock_server_id');
            $table->unique(['mock_server_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_credentials');
    }
};
