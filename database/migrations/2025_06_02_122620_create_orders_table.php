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
        Schema::table('orders', function (Blueprint $table) {
            // Adicionar campos de timestamp para cada status
            // $table->timestamp('confirmed_at')->nullable()->after('created_at');
            // $table->timestamp('preparing_at')->nullable()->after('confirmed_at');
            // $table->timestamp('ready_at')->nullable()->after('preparing_at');
            // $table->timestamp('picked_up_at')->nullable()->after('ready_at');
            // $table->timestamp('delivered_at')->nullable()->after('picked_up_at');
            // $table->timestamp('cancelled_at')->nullable()->after('delivered_at');

            // Campos para rastreamento de entrega
            // $table->decimal('delivery_latitude', 10, 8)->nullable()->after('cancelled_at');
            // $table->decimal('delivery_longitude', 11, 8)->nullable()->after('delivery_latitude');
            // $table->timestamp('location_updated_at')->nullable()->after('delivery_longitude');
            // $table->timestamp('estimated_delivery_time')->nullable()->after('location_updated_at');

            // Campo para razão do cancelamento
            $table->text('cancel_reason')->nullable()->after('estimated_delivery_time');

            // Campo para token de push notification no usuário (se não existir)
            // Nota: Este deve ser adicionado na tabela users, não orders
            // $table->string('push_token')->nullable()->after('cancel_reason');
        });

        // Adicionar campos de push notification na tabela users
        // Schema::table('users', function (Blueprint $table) {
        //     if (!Schema::hasColumn('users', 'push_token')) {
        //         $table->text('push_token')->nullable()->after('remember_token');
        //     }
        //     if (!Schema::hasColumn('users', 'platform')) {
        //         $table->enum('platform', ['ios', 'android'])->nullable()->after('push_token');
        //     }
        // });

        // Verificar se a coluna delivery_fee existe na tabela restaurants
        // Schema::table('restaurants', function (Blueprint $table) {
        //     if (!Schema::hasColumn('restaurants', 'delivery_fee')) {
        //         $table->decimal('delivery_fee', 8, 2)->default(50.00)->after('delivery_time_max');
        //     }
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'confirmed_at',
                'preparing_at',
                'ready_at',
                'picked_up_at',
                'delivered_at',
                'cancelled_at',
                'delivery_latitude',
                'delivery_longitude',
                'location_updated_at',
                'estimated_delivery_time',
                'cancel_reason'
            ]);
        });

        // Schema::table('users', function (Blueprint $table) {
        //     if (Schema::hasColumn('users', 'push_token')) {
        //         $table->dropColumn('push_token');
        //     }
        //     if (Schema::hasColumn('users', 'platform')) {
        //         $table->dropColumn('platform');
        //     }
        // });

        // Schema::table('restaurants', function (Blueprint $table) {
        //     if (Schema::hasColumn('restaurants', 'delivery_fee')) {
        //         $table->dropColumn('delivery_fee');
        //     }
        // });
    }
};
