<?php
// database/migrations/2024_01_15_000003_create_payment_refunds_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->text('reason');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled']);
            $table->timestamp('refunded_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('processed_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index(['payment_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
    }
};