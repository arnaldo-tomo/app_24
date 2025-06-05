<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function __construct()
    {
        // $this->middleware(function ($request, $next) {
        //     $user = auth()->user();
        //     if (!$user || !$user->isRestaurantOwner()) {
        //         abort(403, 'Acesso negado');
        //     }
        //     return $next($request);
        // });
    }

    public function index()
    {
        $restaurant = auth()->user()->restaurants()->first();

        $menu_categories = MenuCategory::where('restaurant_id', $restaurant->id)
                                     ->with(['menuItems' => function($query) {
                                         $query->orderBy('sort_order');
                                     }])
                                     ->orderBy('sort_order')
                                     ->get();

        return view('restaurant.menu.index', compact('restaurant', 'menu_categories'));
    }

    public function createCategory()
    {
        $restaurant = auth()->user()->restaurants()->first();
        return view('restaurant.menu.create-category', compact('restaurant'));
    }

    public function storeCategory(Request $request)
    {
        $restaurant = auth()->user()->restaurants()->first();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['restaurant_id'] = $restaurant->id;
        $validated['slug'] = Str::slug($validated['name']);

        MenuCategory::create($validated);

        return redirect()->route('restaurant.menu.index')
                        ->with('success', 'Categoria criada com sucesso!');
    }

    public function createItem(MenuCategory $menuCategory)
    {
        $restaurant = auth()->user()->restaurants()->first();

        if ($menuCategory->restaurant_id !== $restaurant->id) {
            abort(403, 'Acesso negado');
        }

        return view('restaurant.menu.create-item', compact('restaurant', 'menuCategory'));
    }

    public function storeItem(Request $request, MenuCategory $menuCategory)
    {
        $restaurant = auth()->user()->restaurants()->first();

        if ($menuCategory->restaurant_id !== $restaurant->id) {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_vegetarian' => 'boolean',
            'is_vegan' => 'boolean',
            'is_spicy' => 'boolean',
            'preparation_time' => 'required|integer|min:1',
            'calories' => 'nullable|integer|min:0',
            // 'allergens' => 'nullable|array',
            // 'ingredients' => 'nullable|array',
            'sort_order' => 'integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $validated['restaurant_id'] = $restaurant->id;
        $validated['menu_category_id'] = $menuCategory->id;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_vegetarian'] = $request->has('is_vegetarian');
        $validated['is_vegan'] = $request->has('is_vegan');
        $validated['is_spicy'] = $request->has('is_spicy');

        MenuItem::create($validated);

        return redirect()->route('restaurant.menu.index')
                        ->with('success', 'Item do menu criado com sucesso!');
    }

    public function editItem(MenuItem $menuItem)
    {
        $restaurant = auth()->user()->restaurants()->first();
    //    return $menuItem;
        if ($menuItem->restaurant_id !== $restaurant->id) {
            abort(403, 'Acesso negado');
        }

        // Buscar a categoria específica do item, não a primeira categoria do restaurante
        $menuCategory = $menuItem->menuCategory; // ou $menuItem->category se for esse o nome da relação

        // Também buscar todas as categorias para o select no formulário de edição
        $menuCategories = MenuCategory::where('restaurant_id', $restaurant->id)
                                     ->orderBy('sort_order')
                                     ->get();

        return view('restaurant.menu.edit-item', compact('restaurant', 'menuItem', 'menuCategory', 'menuCategories'));
    }

    public function updateItem(Request $request, MenuItem $menuItem)
    {
        $restaurant = auth()->user()->restaurants()->first();

        if ($menuItem->restaurant_id !== $restaurant->id) {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'menu_category_id' => 'required|exists:menu_categories,id',
            'is_vegetarian' => 'boolean',
            'is_vegan' => 'boolean',
            'is_spicy' => 'boolean',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'preparation_time' => 'required|integer|min:1',
            'calories' => 'nullable|integer|min:0',
            // 'allergens' => 'nullable|array',
            // 'ingredients' => 'nullable|array',
            'sort_order' => 'integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            if ($menuItem->image) {
                Storage::disk('public')->delete($menuItem->image);
            }
            $validated['image'] = $request->file('image')->store('menu-items', 'public');
        }

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_vegetarian'] = $request->has('is_vegetarian');
        $validated['is_vegan'] = $request->has('is_vegan');
        $validated['is_spicy'] = $request->has('is_spicy');
        $validated['is_available'] = $request->has('is_available');
        $validated['is_featured'] = $request->has('is_featured');

        $menuItem->update($validated);

        return redirect()->route('restaurant.menu.index')
                        ->with('success', 'Item do menu atualizado com sucesso!');
    }

    public function toggleItemAvailability(MenuItem $menuItem)
    {
        $restaurant = auth()->user()->restaurants()->first();

        if ($menuItem->restaurant_id !== $restaurant->id) {
            abort(403, 'Acesso negado');
        }

        $menuItem->update(['is_available' => !$menuItem->is_available]);

        $status = $menuItem->is_available ? 'disponível' : 'indisponível';
        return back()->with('success', "Item marcado como {$status}!");
    }
}