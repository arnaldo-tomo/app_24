<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Verificar se colunas já existem antes de adicionar
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->enum('payment_method', ['mpesa', 'emola', 'cash'])->nullable()->after('status');
            }

            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->enum('payment_status', [
                    'pending',
                    'paid',
                    'pending_delivery',
                    'failed',
                    'refunded'
                ])->default('pending')->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_status');
            }

            // Índices para performance
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->index('payment_status');
                $table->index(['user_id', 'payment_status']);
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'payment_reference']);
        });
    }
};