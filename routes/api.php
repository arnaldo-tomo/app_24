<?php

use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API Routes
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Public restaurant data
    Route::get('/restaurants', [RestaurantController::class, 'index']);
    Route::get('/products/search?q={search}', [RestaurantController::class, 'index']);
    Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show']);
    Route::get('/restaurants/{restaurant}/products', [RestaurantController::class, 'getRestaurantProducts']);
    Route::get('/restaurants/featured', [RestaurantController::class, 'featured']);
    Route::post('/restaurants/nearby', [RestaurantController::class, 'nearby']);
    Route::get('/categories', [RestaurantController::class, 'categories']);
});

// Protected API Routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);
    // Route::get('/my-orders', [OrderController::class, 'myOrders']); // Lista de pedidos do usuário
   // Rotas de Pedidos (suas rotas existentes + melhorias)
   Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']); // Lista pedidos
    Route::post('/', [OrderController::class, 'store']); // Criar pedido
    Route::get('{order}', [OrderController::class, 'show']); // Ver pedido
    Route::patch('{order}/cancel', [OrderController::class, 'cancel']); // Cancelar
    Route::get('{order}/track', [OrderController::class, 'track']); // Rastrear

    // Rotas de Pagamento por Pedido
    Route::prefix('{order}/payment')->group(function () {
        Route::post('mpesa', [PaymentController::class, 'initiateMpesaPayment']);
        Route::post('emola', [PaymentController::class, 'initiateMolaPayment']);
        Route::post('cash', [PaymentController::class, 'confirmCashPayment']);
        Route::post('confirm', [PaymentController::class, 'confirmPayment']);
        Route::get('status', [PaymentController::class, 'checkPaymentStatus']);
    });
});


    // Delivery (for delivery persons)
    Route::get('/delivery/available-orders', [DeliveryController::class, 'availableOrders']);
    Route::post('/delivery/orders/{order}/accept', [DeliveryController::class, 'acceptOrder']);
    Route::get('/delivery/my-deliveries', [DeliveryController::class, 'myDeliveries']);
    Route::patch('/delivery/orders/{order}/status', [DeliveryController::class, 'updateDeliveryStatus']);
});

   // Rotas gerais de pagamento
   Route::prefix('payment')->group(function () {
    Route::get('methods', [PaymentController::class, 'getPaymentMethods']);
});


// Webhook público (sem autenticação)
Route::post('webhooks/payment', [PaymentController::class, 'paymentWebhook']);


   // Push tokens
   Route::post('/user/save-push-token', [OrderController::class, 'savePushToken']);
   Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
// Restaurant API Routes (for restaurant owners)
Route::middleware('auth:sanctum')->prefix('v1/restaurant')->group(function () {
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

// API Routes para Ajax/JSON responses
Route::middleware(['auth', 'admin'])->prefix('api/admin')->name('api.admin.')->group(function () {
    Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('reports/chart-data', [ReportsController::class, 'getChartData'])->name('reports.chart-data');
    Route::get('payments/analytics-data', [PaymentController::class, 'getAnalyticsData'])->name('payments.analytics-data');
});