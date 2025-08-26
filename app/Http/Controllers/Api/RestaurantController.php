<?php
// app/Http/Controllers/Api/RestaurantController.php - VERSÃO CORRIGIDA

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    /**
     * ✅ LISTAR RESTAURANTES - Corrigido
     */
    public function index(Request $request)
    {
        try {
            \Log::info('🏪 Buscando restaurantes via API', ['params' => $request->all()]);

            $query = Restaurant::with(['categories'])
                              ->where('is_active', true);

            // Filtro por categoria
            if ($request->has('category_id')) {
                $query->whereHas('categories', function($q) use ($request) {
                    $q->where('categories.id', $request->category_id);
                });
            }

            // Filtro por busca
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            // Filtro por localização (raio)
            if ($request->has('latitude') && $request->has('longitude')) {
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $radius = $request->radius ?? 10; // 10km padrão

                $query->selectRaw("
                    restaurants.*,
                    (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
                ", [$latitude, $longitude, $latitude])
                ->having('distance', '<=', $radius)
                ->orderBy('distance');
            }

            // Ordenação
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

            // Destacados primeiro
            if ($request->has('featured')) {
                $query->orderBy('is_featured', 'desc');
            }

            $restaurants = $query->paginate($request->per_page ?? 15);

            \Log::info('✅ Restaurantes encontrados:', ['count' => $restaurants->count()]);

            // ✅ RETORNO JSON CORRETO
            return response()->json([
                'status' => 'success',
                'message' => 'Restaurantes carregados com sucesso',
                'data' => $restaurants->items(), // Items do paginate
                'pagination' => [
                    'total' => $restaurants->total(),
                    'count' => $restaurants->count(),
                    'per_page' => $restaurants->perPage(),
                    'current_page' => $restaurants->currentPage(),
                    'total_pages' => $restaurants->lastPage(),
                    'has_more' => $restaurants->hasMorePages(),
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao buscar restaurantes:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar restaurantes',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * ✅ EXIBIR RESTAURANTE ESPECÍFICO - Corrigido
     */
    public function show(Restaurant $restaurant)
    {
        try {
            \Log::info('🏪 Buscando restaurante específico:', ['id' => $restaurant->id]);

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
                'message' => 'Restaurante carregado com sucesso',
                'data' => [
                    'restaurant' => $restaurant,
                    'is_open' => $restaurant->isOpen(),
                    'average_delivery_time' => $restaurant->getAverageDeliveryTime() ?? 30
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao buscar restaurante:', [
                'restaurant_id' => $restaurant->id ?? 'N/A',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar restaurante'
            ], 500);
        }
    }

    /**
     * ✅ PRODUTOS DO RESTAURANTE - Corrigido
     */
    public function getRestaurantProducts(Restaurant $restaurant)
    {
        try {
            \Log::info('🍽️ Buscando produtos do restaurante:', ['restaurant_id' => $restaurant->id]);

            if (!$restaurant->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Restaurante não disponível'
                ], 404);
            }

            $products = MenuItem::where('restaurant_id', $restaurant->id)
                               ->where('is_available', true)
                               ->with(['menuCategory'])
                               ->orderBy('sort_order')
                               ->get();

            \Log::info('✅ Produtos encontrados:', ['count' => $products->count()]);

            return response()->json([
                'status' => 'success',
                'message' => 'Produtos carregados com sucesso',
                'data' => $products
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao buscar produtos:', [
                'restaurant_id' => $restaurant->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar produtos'
            ], 500);
        }
    }

    /**
     * ✅ LISTAR CATEGORIAS - Corrigido
     */
    public function categories()
    {
        try {
            \Log::info('📂 Buscando categorias via API');

            $categories = Category::where('is_active', true)
                                 ->orderBy('sort_order')
                                 ->orderBy('name')
                                 ->get();

            \Log::info('✅ Categorias encontradas:', ['count' => $categories->count()]);

            return response()->json([
                'status' => 'success',
                'message' => 'Categorias carregadas com sucesso',
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao buscar categorias:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar categorias',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * ✅ RESTAURANTES EM DESTAQUE
     */
    public function featured(Request $request)
    {
        try {
            \Log::info('⭐ Buscando restaurantes em destaque');

            $restaurants = Restaurant::with(['categories'])
                                   ->where('is_active', true)
                                   ->where('is_featured', true)
                                   ->orderBy('rating', 'desc')
                                   ->limit($request->limit ?? 10)
                                   ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Restaurantes em destaque carregados',
                'data' => $restaurants
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao buscar restaurantes em destaque:', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar restaurantes em destaque'
            ], 500);
        }
    }

    /**
     * ✅ RESTAURANTES PRÓXIMOS
     */
    public function nearby(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:50'
        ]);

        try {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->radius ?? 10; // 10km padrão

            \Log::info('📍 Buscando restaurantes próximos:', [
                'lat' => $latitude,
                'lng' => $longitude,
                'radius' => $radius
            ]);

            $restaurants = Restaurant::with(['categories'])
                                   ->where('is_active', true)
                                   ->selectRaw("
                                       restaurants.*,
                                       (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance
                                   ", [$latitude, $longitude, $latitude])
                                   ->having('distance', '<=', $radius)
                                   ->orderBy('distance')
                                   ->get();

            \Log::info('✅ Restaurantes próximos encontrados:', ['count' => $restaurants->count()]);

            return response()->json([
                'status' => 'success',
                'message' => 'Restaurantes próximos carregados',
                'data' => $restaurants
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Erro ao buscar restaurantes próximos:', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao buscar restaurantes próximos'
            ], 500);
        }
    }
}
