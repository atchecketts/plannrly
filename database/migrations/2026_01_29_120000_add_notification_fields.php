<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add shift reminder tracking fields to shifts table
        Schema::table('shifts', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('status');
            $table->timestamp('hour_reminder_sent_at')->nullable()->after('reminder_sent_at');
        });

        // Add notification settings to tenant_settings table
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->boolean('enable_shift_reminders')->default(true)->after('require_manager_approval');
            $table->boolean('remind_day_before')->default(true)->after('enable_shift_reminders');
            $table->boolean('remind_hours_before')->default(true)->after('remind_day_before');
            $table->integer('remind_hours_before_value')->default(1)->after('remind_hours_before');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['reminder_sent_at', 'hour_reminder_sent_at']);
        });

        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->dropColumn([
                'enable_shift_reminders',
                'remind_day_before',
                'remind_hours_before',
                'remind_hours_before_value',
            ]);
        });
    }
};
