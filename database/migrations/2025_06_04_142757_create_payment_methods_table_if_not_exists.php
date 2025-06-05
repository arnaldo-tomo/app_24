<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Só criar se não existir
        if (!Schema::hasTable('payment_refunds')) {
            Schema::create('payment_refunds', function (Blueprint $table) {
                $table->id();
                $table->string('refund_id')->unique();
                $table->decimal('amount', 10, 2);
                $table->text('reason')->nullable();
                $table->enum('status', [
                    'pending',
                    'processing',
                    'completed',
                    'failed',
                    'cancelled'
                ])->default('pending');
                $table->timestamp('refunded_at')->nullable();
                $table->text('failure_reason')->nullable();
                $table->json('metadata')->nullable();

                // Foreign keys
                $table->foreignId('payment_id')->constrained()->onDelete('cascade');
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');

                $table->timestamps();

                // Índices
                $table->index('status');
                $table->index(['payment_id', 'status']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('payment_refunds');
    }
};