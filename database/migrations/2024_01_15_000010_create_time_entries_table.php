<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->timestamp('clock_in_at')->nullable();
            $table->timestamp('clock_out_at')->nullable();
            $table->timestamp('break_start_at')->nullable();
            $table->timestamp('break_end_at')->nullable();
            $table->unsignedSmallInteger('actual_break_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->json('clock_in_location')->nullable();
            $table->json('clock_out_location')->nullable();
            $table->string('status')->default('clocked_in');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('user_id');
            $table->index('shift_id');
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
