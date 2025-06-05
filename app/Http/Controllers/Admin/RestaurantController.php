<?php

// app/Http/Controllers/Admin/RestaurantController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(function ($request, $next) {
    //         if (!auth()->user() || !auth()->user()->isAdmin()) {
    //             abort(403, 'Acesso negado');
    //         }
    //         return $next($request);
    //     });
    // }

    public function index()
    {
        $restaurants = Restaurant::with(['owner', 'categories'])
                                ->withCount('orders')
                                ->latest()
                                ->paginate(20);

        return view('admin.restaurants.index', compact('restaurants'));
    }

    public function create()
    {
        $owners = User::where('role', 'restaurant_owner')->get();
        $categories = Category::where('is_active', true)->get();

        return view('admin.restaurants.create', compact('owners', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:restaurants,email',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delivery_fee' => 'required|numeric|min:0',
            'delivery_time_min' => 'required|integer|min:1',
            'delivery_time_max' => 'required|integer|min:1',
            'minimum_order' => 'required|numeric|min:0',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i',
            'working_days' => 'required|array',
            'working_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'user_id' => 'required|exists:users,id',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('restaurants', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('restaurants/covers', 'public');
        }

        $validated['slug'] = Str::slug($validated['name']);

        $restaurant = Restaurant::create($validated);

        if ($request->has('categories')) {
            $restaurant->categories()->sync($request->categories);
        }

        return redirect()->route('admin.restaurants.index')
                        ->with('success', 'Restaurante criado com sucesso!');
    }

    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['owner', 'categories', 'menuCategories.menuItems', 'orders' => function($query) {
            $query->latest()->take(10);
        }]);

        return view('admin.restaurants.show', compact('restaurant'));
    }

    public function edit(Restaurant $restaurant)
    {
        $owners = User::where('role', 'restaurant_owner')->get();
        $categories = Category::where('is_active', true)->get();

        return view('admin.restaurants.edit', compact('restaurant', 'owners', 'categories'));
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:restaurants,email,' . $restaurant->id,
            'address' => 'required|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delivery_fee' => 'required|numeric|min:0',
            'delivery_time_min' => 'required|integer|min:1',
            'delivery_time_max' => 'required|integer|min:1',
            'minimum_order' => 'required|numeric|min:0',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i',
            'working_days' => 'required|array',
            'working_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'user_id' => 'required|exists:users,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
        ]);

        if ($request->hasFile('image')) {
            if ($restaurant->image) {
                Storage::disk('public')->delete($restaurant->image);
            }
            $validated['image'] = $request->file('image')->store('restaurants', 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($restaurant->cover_image) {
                Storage::disk('public')->delete($restaurant->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('restaurants/covers', 'public');
        }

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        $restaurant->update($validated);

        if ($request->has('categories')) {
            $restaurant->categories()->sync($request->categories);
        }

        return redirect()->route('admin.restaurants.index')
                        ->with('success', 'Restaurante atualizado com sucesso!');
    }

    public function destroy(Restaurant $restaurant)
    {
        if ($restaurant->image) {
            Storage::disk('public')->delete($restaurant->image);
        }

        if ($restaurant->cover_image) {
            Storage::disk('public')->delete($restaurant->cover_image);
        }

        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')
                        ->with('success', 'Restaurante excluído com sucesso!');
    }

    public function toggleStatus(Restaurant $restaurant)
    {
        $restaurant->update(['is_active' => !$restaurant->is_active]);

        $status = $restaurant->is_active ? 'ativado' : 'desativado';
        return back()->with('success', "Restaurante {$status} com sucesso!");
    }

    public function toggleFeatured(Restaurant $restaurant)
    {
        $restaurant->update(['is_featured' => !$restaurant->is_featured]);

        $status = $restaurant->is_featured ? 'destacado' : 'removido dos destaques';
        return back()->with('success', "Restaurante {$status} com sucesso!");
    }
}