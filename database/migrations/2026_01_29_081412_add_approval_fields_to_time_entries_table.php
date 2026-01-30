<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('time_entries', 'approved_by')) {
            Schema::table('time_entries', function (Blueprint $table) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('time_entries', 'approved_at')) {
            Schema::table('time_entries', function (Blueprint $table) {
                $table->timestamp('approved_at')->nullable();
            });
        }
        if (! Schema::hasColumn('time_entries', 'adjustment_reason')) {
            Schema::table('time_entries', function (Blueprint $table) {
                $table->text('adjustment_reason')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'adjustment_reason']);
        });
    }
};
