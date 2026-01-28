<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('recurring');
            $table->tinyInteger('day_of_week')->nullable();
            $table->date('specific_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_available')->default(true);
            $table->string('preference_level')->default('available');
            $table->text('notes')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'day_of_week']);
            $table->index(['user_id', 'specific_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_availability');
    }
};
