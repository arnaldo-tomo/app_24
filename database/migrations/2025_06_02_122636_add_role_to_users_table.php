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
            $table->enum('role', ['admin', 'restaurant_owner', 'customer', 'delivery_person'])
            ->default('customer')
            ->after('email');
      $table->string('phone')->nullable()->after('email');
      $table->text('address')->nullable()->after('phone');
      $table->decimal('latitude', 10, 8)->nullable()->after('address');
      $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
      $table->string('avatar')->nullable()->after('longitude');
      $table->boolean('is_active')->default(true)->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn(['role', 'phone', 'address', 'latitude', 'longitude', 'avatar', 'is_active']);
        });
    }
};
