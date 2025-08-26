<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\DeliveryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Versão Atualizada com Funcionalidades de Proximidade
|--------------------------------------------------------------------------
*/

// Webhook público (sem autenticação)
Route::post('webhooks/payment', [PaymentController::class, 'paymentWebhook']);

// Rotas de autenticação
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Rotas autenticadas
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::patch('/profile', [AuthController::class, 'updateProfile']);
    });
});

// Rotas gerais de pagamento (públicas)
Route::prefix('v1/payment')->group(function () {
    Route::get('methods', [PaymentController::class, 'getPaymentMethods']);
});

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // =============== ROTAS DE DELIVERY - ATUALIZADAS ===============
    Route::prefix('delivery')->group(function () {
        // Pedidos disponíveis com lógica de proximidade
        Route::get('available-orders', [DeliveryController::class, 'availableOrders']);

        // Aceitar pedido
        Route::post('orders/{order}/accept', [DeliveryController::class, 'acceptOrder']);

        // Minhas entregas (histórico)
        Route::get('my-deliveries', [DeliveryController::class, 'myDeliveries']);

        // Atualizar status da entrega
        Route::patch('orders/{order}/status', [DeliveryController::class, 'updateDeliveryStatus']);

        // NOVA: Atualizar localização do entregador
        Route::post('update-location', [DeliveryController::class, 'updateLocation']);

        // NOVA: Estatísticas do entregador
        Route::get('stats', [DeliveryController::class, 'getDeliveryStats']);

        // NOVA: Configurações de raio
        Route::get('settings', [DeliveryController::class, 'getSettings']);
        Route::patch('settings', [DeliveryController::class, 'updateSettings']);
    });

    // =============== ROTAS DE PEDIDOS (CLIENTES) ===============
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('{order}', [OrderController::class, 'show']);
        Route::patch('{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('{order}/track', [OrderController::class, 'track']);

        // Rotas de Pagamento por Pedido
        Route::prefix('{order}/payment')->group(function () {
            Route::post('mpesa', [PaymentController::class, 'initiateMpesaPayment']);
            Route::post('emola', [PaymentController::class, 'initiateMolaPayment']);
            Route::post('cash', [PaymentController::class, 'confirmCashPayment']);
            Route::post('confirm', [PaymentController::class, 'confirmPayment']);
            Route::get('status', [PaymentController::class, 'checkPaymentStatus']);
        });
    });

    // Push tokens para notificações
    Route::post('/user/save-push-token', [OrderController::class, 'savePushToken']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
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
