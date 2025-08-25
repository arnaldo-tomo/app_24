<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Relacionamento com pedido
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            // Informações básicas do pagamento
            $table->enum('payment_method', ['cash', 'mpesa', 'emola', 'card'])->index();
            $table->decimal('amount', 10, 2);
            $table->decimal('fee_amount', 8, 2)->nullable()->default(0);
            $table->decimal('net_amount', 10, 2)->nullable();

            // Status do pagamento
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
                'refunded'
            ])->default('pending')->index();

            // IDs de transação
            $table->string('transaction_id')->nullable()->index();
            $table->string('external_transaction_id')->nullable()->index();

            // Datas
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('confirmed_at')->nullable();

            // Dados adicionais (JSON)
            $table->json('confirmation_data')->nullable(); // Dados da confirmação
            $table->json('external_data')->nullable();     // Dados do provedor externo

            // Observações
            $table->text('notes')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['order_id', 'status']);
            $table->index(['payment_method', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
