<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('occasions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_name');
            $table->string('relation_type');
            $table->string('occasion_name');
            $table->date('occasion_date');
            $table->boolean('reminder_status')->default(true);
            $table->unsignedTinyInteger('reminder_days_before')->default(7);
            $table->foreignId('preferred_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->text('preferred_bouquet_notes')->nullable();
            $table->timestamp('last_reminded_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'occasion_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occasions');
    }
};
