<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('staffing_requirements')) {
            return;
        }

        Schema::create('staffing_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('business_role_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week'); // 0=Sunday, 6=Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('min_employees')->default(0);
            $table->unsignedInteger('max_employees')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Composite index for efficient lookups
            $table->index(['tenant_id', 'day_of_week', 'is_active']);
            $table->index(['tenant_id', 'business_role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staffing_requirements');
    }
};
