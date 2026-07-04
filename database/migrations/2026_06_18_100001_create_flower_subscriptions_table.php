<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flower_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_subscription_id')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable();
            $table->enum('frequency', ['weekly', 'monthly'])->default('monthly');
            $table->string('delivery_day')->default('monday');
            $table->string('preferred_color_palette')->nullable();
            $table->enum('status', ['active', 'paused', 'cancelled', 'past_due'])->default('active');
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('next_delivery_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flower_subscriptions');
    }
};
