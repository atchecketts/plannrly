<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('enable_clock_in_out')->default(false);
            $table->boolean('enable_shift_acknowledgement')->default(false);
            $table->time('day_starts_at')->default('00:00:00');
            $table->time('day_ends_at')->default('23:59:59');
            $table->tinyInteger('week_starts_on')->default(1); // 0=Sun, 1=Mon, etc.
            $table->string('timezone', 64)->default('UTC');
            $table->string('date_format', 32)->default('Y-m-d');
            $table->string('time_format', 16)->default('H:i');
            $table->unsignedSmallInteger('missed_grace_minutes')->default(15);
            $table->boolean('notify_on_publish')->default(true);
            $table->boolean('require_admin_approval_for_swaps')->default(true);
            $table->string('leave_carryover_mode')->default('none');
            $table->string('default_currency', 3)->default('GBP');
            $table->string('primary_color', 7)->default('#6366f1');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
