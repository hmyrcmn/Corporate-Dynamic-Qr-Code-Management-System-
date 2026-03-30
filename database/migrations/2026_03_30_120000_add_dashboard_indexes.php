<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table): void {
            $table->index(['department_id', 'created_at'], 'qr_codes_department_created_at_idx');
            $table->index(['department_id', 'is_active', 'created_at'], 'qr_codes_department_active_created_at_idx');
            $table->index(['is_active', 'created_at'], 'qr_codes_active_created_at_idx');
        });

        Schema::table('scan_analytics', function (Blueprint $table): void {
            $table->index(['qr_code_id', 'timestamp'], 'scan_analytics_qr_timestamp_idx');
        });
    }

    public function down(): void
    {
        Schema::table('scan_analytics', function (Blueprint $table): void {
            $table->dropIndex('scan_analytics_qr_timestamp_idx');
        });

        Schema::table('qr_codes', function (Blueprint $table): void {
            $table->dropIndex('qr_codes_department_created_at_idx');
            $table->dropIndex('qr_codes_department_active_created_at_idx');
            $table->dropIndex('qr_codes_active_created_at_idx');
        });
    }
};
