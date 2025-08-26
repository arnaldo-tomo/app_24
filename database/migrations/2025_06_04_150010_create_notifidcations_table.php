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
        Schema::table('users', function (Blueprint $table) {
            // Verificar se as colunas já existem antes de adicionar
            if (!Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('address');
            }

            if (!Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }

            // Adicionar índices para consultas de proximidade mais eficientes
            if (!Schema::hasIndex('users', ['latitude', 'longitude'])) {
                $table->index(['latitude', 'longitude'], 'idx_user_coordinates');
            }

            // Adicionar configurações para entregadores
            if (!Schema::hasColumn('users', 'delivery_radius_km')) {
                $table->decimal('delivery_radius_km', 4, 1)->default(5.0)->after('longitude')
                      ->comment('Raio de entrega preferido em quilômetros');
            }

            if (!Schema::hasColumn('users', 'is_online')) {
                $table->boolean('is_online')->default(false)->after('delivery_radius_km')
                      ->comment('Status online para entregadores');
            }

            if (!Schema::hasColumn('users', 'last_location_update')) {
                $table->timestamp('last_location_update')->nullable()->after('is_online')
                      ->comment('Última atualização de localização');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_user_coordinates');
            $table->dropColumn([
                'latitude',
                'longitude',
                'delivery_radius_km',
                'is_online',
                'last_location_update'
            ]);
        });
    }
};
