<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'clock_in_grace_minutes' => fn (Blueprint $table) => $table->unsignedSmallInteger('clock_in_grace_minutes')->default(15),
            'require_gps_clock_in' => fn (Blueprint $table) => $table->boolean('require_gps_clock_in')->default(false),
            'auto_clock_out_enabled' => fn (Blueprint $table) => $table->boolean('auto_clock_out_enabled')->default(false),
            'auto_clock_out_time' => fn (Blueprint $table) => $table->time('auto_clock_out_time')->nullable(),
            'overtime_threshold_minutes' => fn (Blueprint $table) => $table->unsignedSmallInteger('overtime_threshold_minutes')->default(480),
            'require_manager_approval' => fn (Blueprint $table) => $table->boolean('require_manager_approval')->default(false),
        ];

        foreach ($columns as $column => $definition) {
            if (! Schema::hasColumn('tenant_settings', $column)) {
                Schema::table('tenant_settings', function (Blueprint $table) use ($definition) {
                    $definition($table);
                });
            }
        }
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
