<?php

namespace Database\Seeders;

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@deliverysystem.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '+258841234567',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Categories
        $categories = [
            ['name' => 'Pizza', 'icon' => '🍕', 'sort_order' => 1],
            ['name' => 'Hambúrguer', 'icon' => '🍔', 'sort_order' => 2],
            ['name' => 'Frango', 'icon' => '🍗', 'sort_order' => 3],
            ['name' => 'Massa', 'icon' => '🍝', 'sort_order' => 4],
            ['name' => 'Chinesa', 'icon' => '🥡', 'sort_order' => 5],
            ['name' => 'Bebidas', 'icon' => '🥤', 'sort_order' => 6],
            ['name' => 'Sobremesas', 'icon' => '🍰', 'sort_order' => 7],
            ['name' => 'Saudável', 'icon' => '🥗', 'sort_order' => 8],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create Restaurant Owner
        $restaurantOwner = User::create([
            'name' => 'João Silva',
            'email' => 'joao@bellavista.com',
            'password' => Hash::make('password123'),
            'role' => 'restaurant_owner',
            'phone' => '+258843456789',
            'address' => 'Av. 25 de Setembro, 1234, Maputo',
            'latitude' => -25.9653,
            'longitude' => 32.5892,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Sample Restaurant
        $restaurant = Restaurant::create([
            'name' => 'Bella Vista Pizzaria',
            'description' => 'A melhor pizzaria de Maputo com ingredientes frescos e sabores autênticos.',
            'phone' => '+258843456789',
            'email' => 'joao@bellavista.com',
            'address' => 'Av. 25 de Setembro, 1234, Maputo',
            'latitude' => -25.9653,
            'longitude' => 32.5892,
            'delivery_fee' => 50.00,
            'delivery_time_min' => 30,
            'delivery_time_max' => 45,
            'minimum_order' => 150.00,
            'rating' => 4.8,
            'total_reviews' => 127,
            'is_active' => true,
            'is_featured' => true,
            'opening_time' => '10:00',
            'closing_time' => '23:00',
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'user_id' => $restaurantOwner->id,
        ]);

        // Associate restaurant with pizza category
        $pizzaCategory = Category::where('name', 'Pizza')->first();
        $restaurant->categories()->attach($pizzaCategory->id);

        // Create Delivery Person
        $deliveryPerson = User::create([
            'name' => 'Carlos Manjate',
            'email' => 'carlos@entregador.com',
            'password' => Hash::make('password123'),
            'role' => 'delivery_person',
            'phone' => '+258845678901',
            'address' => 'Bairro Central, Maputo',
            'latitude' => -25.9692,
            'longitude' => 32.5731,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create Customer
        $customer = User::create([
            'name' => 'Maria Santos',
            'email' => 'maria@cliente.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'phone' => '+258847890123',
            'address' => 'Av. Julius Nyerere, 567, Maputo',
            'latitude' => -25.9581,
            'longitude' => 32.5831,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Users created successfully!');
        $this->command->info('Admin: admin@deliverysystem.com / password123');
        $this->command->info('Restaurant Owner: joao@bellavista.com / password123');
        $this->command->info('Delivery Person: carlos@entregador.com / password123');
        $this->command->info('Customer: maria@cliente.com / password123');
    }
}
