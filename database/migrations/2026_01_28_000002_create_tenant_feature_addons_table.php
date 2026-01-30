<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_feature_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('feature');
            $table->timestamp('enabled_at');
            $table->timestamp('expires_at')->nullable();
            $table->string('stripe_subscription_item_id')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'feature']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_feature_addons');
    }
};
