<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\MenuCategory;
use App\Models\MenuItem;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::where('name', 'Bella Vista Pizzaria')->first();

        if (!$restaurant) {
            $this->command->error('Restaurant not found! Run UserSeeder first.');
            return;
        }

        // Create Menu Categories
        $pizzaCategory = MenuCategory::create([
            'name' => 'Pizzas',
            'description' => 'Nossas deliciosas pizzas artesanais',
            'sort_order' => 1,
            'restaurant_id' => $restaurant->id,
        ]);

        $drinkCategory = MenuCategory::create([
            'name' => 'Bebidas',
            'description' => 'Bebidas refrescantes',
            'sort_order' => 2,
            'restaurant_id' => $restaurant->id,
        ]);

        $dessertCategory = MenuCategory::create([
            'name' => 'Sobremesas',
            'description' => 'Doces irresistíveis',
            'sort_order' => 3,
            'restaurant_id' => $restaurant->id,
        ]);

        // Create Pizza Items
        $pizzas = [
            [
                'name' => 'Pizza Margherita',
                'description' => 'Molho de tomate, mozzarella, manjericão fresco e azeite',
                'price' => 450.00,
                'preparation_time' => 25,
                'is_vegetarian' => true,
                'calories' => 850,
                'ingredients' => ['Molho de tomate', 'Mozzarella', 'Manjericão', 'Azeite'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Pizza Pepperoni',
                'description' => 'Molho de tomate, mozzarella e pepperoni',
                'price' => 520.00,
                'preparation_time' => 25,
                'calories' => 950,
                'ingredients' => ['Molho de tomate', 'Mozzarella', 'Pepperoni'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Pizza Quattro Stagioni',
                'description' => 'Molho de tomate, mozzarella, presunto, cogumelos, alcachofras e azeitonas',
                'price' => 580.00,
                'preparation_time' => 30,
                'calories' => 920,
                'ingredients' => ['Molho de tomate', 'Mozzarella', 'Presunto', 'Cogumelos', 'Alcachofras', 'Azeitonas'],
                'sort_order' => 3,
            ],
            [
                'name' => 'Pizza Vegetariana',
                'description' => 'Molho de tomate, mozzarella, pimentão, cebola, tomate e oregano',
                'price' => 480.00,
                'discount_price' => 420.00,
                'preparation_time' => 25,
                'is_vegetarian' => true,
                'calories' => 780,
                'ingredients' => ['Molho de tomate', 'Mozzarella', 'Pimentão', 'Cebola', 'Tomate', 'Oregano'],
                'sort_order' => 4,
            ],
        ];

        foreach ($pizzas as $pizza) {
            $pizza['restaurant_id'] = $restaurant->id;
            $pizza['menu_category_id'] = $pizzaCategory->id;
            MenuItem::create($pizza);
        }

        // Create Drink Items
        $drinks = [
            [
                'name' => 'Coca-Cola 500ml',
                'description' => 'Refrigerante gelado',
                'price' => 80.00,
                'preparation_time' => 2,
                'calories' => 210,
                'sort_order' => 1,
            ],
            [
                'name' => 'Água Mineral 500ml',
                'description' => 'Água mineral natural',
                'price' => 50.00,
                'preparation_time' => 1,
                'calories' => 0,
                'sort_order' => 2,
            ],
            [
                'name' => 'Sumo de Laranja Natural',
                'description' => 'Sumo de laranja fresco natural',
                'price' => 120.00,
                'preparation_time' => 5,
                'calories' => 150,
                'sort_order' => 3,
            ],
        ];

        foreach ($drinks as $drink) {
            $drink['restaurant_id'] = $restaurant->id;
            $drink['menu_category_id'] = $drinkCategory->id;
            MenuItem::create($drink);
        }

        // Create Dessert Items
        $desserts = [
            [
                'name' => 'Tiramisu',
                'description' => 'Sobremesa italiana clássica com café e mascarpone',
                'price' => 180.00,
                'preparation_time' => 10,
                'calories' => 450,
                'ingredients' => ['Mascarpone', 'Café', 'Biscoitos', 'Cacau'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Gelato Vanilla',
                'description' => 'Gelato artesanal de baunilha',
                'price' => 120.00,
                'preparation_time' => 5,
                'calories' => 250,
                'sort_order' => 2,
            ],
        ];

        foreach ($desserts as $dessert) {
            $dessert['restaurant_id'] = $restaurant->id;
            $dessert['menu_category_id'] = $dessertCategory->id;
            MenuItem::create($dessert);
        }

        $this->command->info('Menu items created successfully!');
    }
}