<?php
// database/migrations/2025_08_27_add_delivery_fields_to_users_table.php

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
            // Campos para entregadores
            // $table->decimal('delivery_radius_km', 5, 2)->default(5.00)->after('longitude');
            $table->timestamp('location_updated_at')->nullable()->after('delivery_radius_km');
            $table->timestamp('last_online_at')->nullable()->after('location_updated_at');

            // Índices para performance
            // $table->index(['role', 'is_active']);
            // $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'is_active']);
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropColumn([
                'delivery_radius_km',
                'location_updated_at',
                'last_online_at'
            ]);
        });
    }
};
