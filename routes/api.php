<?php
use App\Http\Controllers\Api\{AuthController, RestaurantController, OrderController, PaymentController, DeliveryController};
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

// 🔓 ROTAS PÚBLICAS (sem autenticação)
Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Dados públicos
    Route::get('/restaurants', [RestaurantController::class, 'index']);
    Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show']);
    Route::get('/restaurants/{restaurant}/products', [RestaurantController::class, 'getRestaurantProducts']);
    Route::get('/categories', [RestaurantController::class, 'categories']);
    Route::get('/restaurants/featured', [RestaurantController::class, 'featured']);
    Route::post('/restaurants/nearby', [RestaurantController::class, 'nearby']);
});

// 🔐 ROTAS PROTEGIDAS (com autenticação)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Pedidos
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::get('/orders/{order}/track', [OrderController::class, 'track']);

    // Pagamentos
    Route::get('/payment/methods', [PaymentController::class, 'getPaymentMethods']);
    Route::post('/orders/{order}/payment', [PaymentController::class, 'processPayment']);

    // Push tokens
    Route::post('/user/save-push-token', [OrderController::class, 'savePushToken']);
});

// 🚚 ROTAS DO ENTREGADOR
Route::middleware('auth:sanctum')->prefix('v1/delivery')->group(function () {
    Route::get('/available-orders', [DeliveryController::class, 'availableOrders']);
    Route::post('/orders/{order}/accept', [DeliveryController::class, 'acceptOrder']);
    Route::get('/my-deliveries', [DeliveryController::class, 'myDeliveries']);
    Route::patch('/orders/{order}/status', [DeliveryController::class, 'updateDeliveryStatus']);
});

// 🏪 ROTAS DO RESTAURANTE
Route::middleware('auth:sanctum')->prefix('v1/restaurant')->group(function () {
    Route::get('/dashboard/stats', [RestaurantController::class, 'getStats']);
    Route::get('/orders', [RestaurantController::class, 'getRestaurantOrders']);
    Route::patch('/orders/{order}/status', [RestaurantController::class, 'updateOrderStatus']);
    Route::get('/menu', [RestaurantController::class, 'getMenu']);
});
// =============== ROTAS DE RESTAURANTE (API MOBILE) ===============
Route::middleware('auth:sanctum')->prefix('v1/restaurant')->group(function () {

    // Dashboard - Estatísticas básicas
    Route::get('/dashboard/stats', function (Request $request) {
        $user = $request->user();

        if (!$user->isRestaurantOwner()) {
            return response()->json(['status' => 'error', 'message' => 'Acesso negado'], 403);
        }

        $restaurant = $user->restaurants()->first();
        if (!$restaurant) {
            return response()->json(['status' => 'error', 'message' => 'Restaurante não encontrado'], 404);
        }

        $stats = [
            'orders_today' => \App\Models\Order::where('restaurant_id', $restaurant->id)
                                               ->whereDate('created_at', today())
                                               ->count(),
            'revenue_today' => \App\Models\Order::where('restaurant_id', $restaurant->id)
                                                ->whereDate('created_at', today())
                                                ->where('payment_status', 'paid')
                                                ->sum('total_amount'),
            'pending_orders' => \App\Models\Order::where('restaurant_id', $restaurant->id)
                                                 ->where('status', 'pending')
                                                 ->count(),
            'active_menu_items' => \App\Models\MenuItem::where('restaurant_id', $restaurant->id)
                                                      ->where('is_available', true)
                                                      ->count(),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    });

    // Pedidos do restaurante
    Route::get('/orders', function (Request $request) {
        $user = $request->user();

        if (!$user->isRestaurantOwner()) {
            return response()->json(['status' => 'error', 'message' => 'Acesso negado'], 403);
        }

        $restaurant = $user->restaurants()->first();
        if (!$restaurant) {
            return response()->json(['status' => 'error', 'message' => 'Restaurante não encontrado'], 404);
        }

        $orders = \App\Models\Order::where('restaurant_id', $restaurant->id)
                                  ->with(['customer', 'items.menuItem'])
                                  ->latest()
                                  ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    });

    // Atualizar status do pedido
    Route::patch('/orders/{order}/status', function (Request $request, \App\Models\Order $order) {
        $user = $request->user();

        if (!$user->isRestaurantOwner()) {
            return response()->json(['status' => 'error', 'message' => 'Acesso negado'], 403);
        }

        $restaurant = $user->restaurants()->first();
        if (!$restaurant || $order->restaurant_id !== $restaurant->id) {
            return response()->json(['status' => 'error', 'message' => 'Acesso negado'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,picked_up,delivered,cancelled'
        ]);

        $order->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Status do pedido atualizado',
            'data' => [
                'order' => $order->fresh(['customer', 'items.menuItem'])
            ]
        ]);
    });

    // Menu do restaurante
    Route::get('/menu', function (Request $request) {
        $user = $request->user();

        if (!$user->isRestaurantOwner()) {
            return response()->json(['status' => 'error', 'message' => 'Acesso negado'], 403);
        }

        $restaurant = $user->restaurants()->first();
        if (!$restaurant) {
            return response()->json(['status' => 'error', 'message' => 'Restaurante não encontrado'], 404);
        }

        $menu_categories = \App\Models\MenuCategory::where('restaurant_id', $restaurant->id)
                                                  ->with(['menuItems' => function($query) {
                                                      $query->orderBy('sort_order');
                                                  }])
                                                  ->orderBy('sort_order')
                                                  ->get();

        return response()->json([
            'status' => 'success',
            'data' => $menu_categories
        ]);
    });

    // Atualizar disponibilidade de item do menu
    Route::patch('/menu-items/{menuItem}/availability', function (Request $request, \App\Models\MenuItem $menuItem) {
        $user = $request->user();

        if (!$user->isRestaurantOwner()) {
            return response()->json(['status' => 'error', 'message' => 'Acesso negado'], 403);
        }

        $restaurant = $user->restaurants()->first();
        if (!$restaurant || $menuItem->restaurant_id !== $restaurant->id) {
            return response()->json(['status' => 'error', 'message' => 'Acesso negado'], 403);
        }

        $menuItem->update(['is_available' => !$menuItem->is_available]);

        return response()->json([
            'status' => 'success',
            'message' => 'Disponibilidade do item atualizada',
            'data' => [
                'menu_item' => $menuItem
            ]
        ]);
    });
});

// =============== ROTAS DE ADMIN ===============
Route::middleware(['auth', 'admin'])->prefix('api/admin')->name('api.admin.')->group(function () {
    Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('reports/chart-data', [ReportsController::class, 'getChartData'])->name('reports.chart-data');
    Route::get('payments/analytics-data', [PaymentController::class, 'getAnalyticsData'])->name('payments.analytics-data');
});
