<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add shift reminder tracking fields to shifts table
        if (! Schema::hasColumn('shifts', 'reminder_sent_at')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->timestamp('reminder_sent_at')->nullable()->after('status');
            });
        }
        if (! Schema::hasColumn('shifts', 'hour_reminder_sent_at')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->timestamp('hour_reminder_sent_at')->nullable()->after('reminder_sent_at');
            });
        }

        // Add notification settings to tenant_settings table
        $tenantColumns = [
            'enable_shift_reminders' => fn (Blueprint $table) => $table->boolean('enable_shift_reminders')->default(true),
            'remind_day_before' => fn (Blueprint $table) => $table->boolean('remind_day_before')->default(true),
            'remind_hours_before' => fn (Blueprint $table) => $table->boolean('remind_hours_before')->default(true),
            'remind_hours_before_value' => fn (Blueprint $table) => $table->integer('remind_hours_before_value')->default(1),
        ];

        foreach ($tenantColumns as $column => $definition) {
            if (! Schema::hasColumn('tenant_settings', $column)) {
                Schema::table('tenant_settings', function (Blueprint $table) use ($definition) {
                    $definition($table);
                });
            }
        }
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
