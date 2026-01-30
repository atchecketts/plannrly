<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('clock_in_grace_minutes')->default(15);
            $table->boolean('require_gps_clock_in')->default(false);
            $table->boolean('auto_clock_out_enabled')->default(false);
            $table->time('auto_clock_out_time')->nullable();
            $table->unsignedSmallInteger('overtime_threshold_minutes')->default(480);
            $table->boolean('require_manager_approval')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->dropColumn([
                'clock_in_grace_minutes',
                'require_gps_clock_in',
                'auto_clock_out_enabled',
                'auto_clock_out_time',
                'overtime_threshold_minutes',
                'require_manager_approval',
            ]);
        });
    }
};
