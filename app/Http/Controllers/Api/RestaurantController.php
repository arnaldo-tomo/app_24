<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = Restaurant::with(['categories'])
                          ->where('is_active', true);

        // Filter by category
        if ($request->has('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Filter by search term
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by location (within radius)
        if ($request->has('latitude') && $request->has('longitude')) {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->radius ?? 10; // Default 10km radius

            $query->selectRaw("
                restaurants.*,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
            ", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
        }

        // Sort options
        $sortBy = $request->sort_by ?? 'rating';
        switch ($sortBy) {
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'delivery_time':
                $query->orderBy('delivery_time_min', 'asc');
                break;
            case 'delivery_fee':
                $query->orderBy('delivery_fee', 'asc');
                break;
            case 'newest':
                $query->latest();
                break;
            default:
                $query->orderBy('rating', 'desc');
        }

        // Featured restaurants first
        if ($request->has('featured')) {
            $query->orderBy('is_featured', 'desc');
        }

     return $restaurants = $query->paginate($request->per_page ?? 15);

        // return response()->json([
        //     'status' => 'success',
        //     'data' => $restaurants
        // ]);
    }

    public function show(Restaurant $restaurant)
    {
        if (!$restaurant->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Restaurante não disponível'
            ], 404);
        }

        $restaurant->load([
            'categories',
            'menuCategories' => function($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            },
            'menuCategories.menuItems' => function($query) {
                $query->where('is_available', true)->orderBy('sort_order');
            }
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'restaurant' => $restaurant,
                'is_open' => $restaurant->isOpen(),
                'average_delivery_time' => $restaurant->getAverageDeliveryTime()
            ]
        ]);
    }
    public function getRestaurantProducts(Restaurant $restaurant)
    {

        // if (!$restaurant->is_active) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Restaurante não disponível'
        //     ], 404);
        // }

        return $cardaipios= MenuItem::where('restaurant_id',$restaurant->id)->get();

        // $restaurant->load([
        //     'menuItems' => function($query) {
        //         $query->where('is_available', true)->orderBy('sort_order');
        //     }
        // ]);

        // return response()->json([
        //     'status' => 'success',
        //     'data' => [
        //         'restaurant' => $restaurant,
        //         'is_open' => $restaurant->isOpen(),
        //         'average_delivery_time' => $restaurant->getAverageDeliveryTime()
        //     ]
        // ]);
    }

    public function categories()
    {
        $categories = Category::where('is_active', true)
                             ->orderBy('sort_order')
                             ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function featured()
    {
        $restaurants = Restaurant::with(['categories'])
                                ->where('is_active', true)
                                ->where('is_featured', true)
                                ->orderBy('rating', 'desc')
                                ->take(10)
                                ->get();

        return response()->json([
            'status' => 'success',
            'data' => $restaurants
        ]);
    }

    public function nearby(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'sometimes|numeric|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados de validação inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 10;

        $restaurants = Restaurant::with(['categories'])
                                ->where('is_active', true)
                                ->selectRaw("
                                    restaurants.*,
                                    (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
                                ", [$latitude, $longitude, $latitude])
                                ->having('distance', '<=', $radius)
                                ->orderBy('distance')
                                ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $restaurants
        ]);
    }
}
