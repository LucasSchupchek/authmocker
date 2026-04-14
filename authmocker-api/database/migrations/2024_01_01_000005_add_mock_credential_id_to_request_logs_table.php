<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_logs', function (Blueprint $table) {
            $table->uuid('mock_credential_id')->nullable()->after('mock_endpoint_id');

            $table->foreign('mock_credential_id')
                ->references('id')
                ->on('mock_credentials')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('request_logs', function (Blueprint $table) {
            $table->dropForeign(['mock_credential_id']);
            $table->dropColumn('mock_credential_id');
        });
    }
};
