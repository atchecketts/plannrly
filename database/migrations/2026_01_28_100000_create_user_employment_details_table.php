<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_employment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('employment_start_date')->nullable();
            $table->date('employment_end_date')->nullable();
            $table->date('final_working_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->string('employment_status')->default('active');
            $table->string('pay_type')->default('hourly');
            $table->decimal('base_hourly_rate', 10, 2)->nullable();
            $table->decimal('annual_salary', 12, 2)->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->decimal('target_hours_per_week', 5, 2)->nullable();
            $table->decimal('min_hours_per_week', 5, 2)->nullable();
            $table->decimal('max_hours_per_week', 5, 2)->nullable();
            $table->boolean('overtime_eligible')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('employment_status');
            $table->index('final_working_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_employment_details');
    }
};
