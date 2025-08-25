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
    Route::get('/restaurants/search', [RestaurantController::class, 'search']);
    Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show']);
    Route::get('/restaurants/{restaurant}/menu', [RestaurantController::class, 'getMenu']);
    Route::get('/restaurants/{restaurant}/products', [RestaurantController::class, 'getRestaurantProducts']);
    Route::get('/restaurants/featured', [RestaurantController::class, 'featured']);
    Route::post('/restaurants/nearby', [RestaurantController::class, 'nearby']);
    Route::get('/categories', [RestaurantController::class, 'categories']);

    // Health check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'API funcionando corretamente',
            'timestamp' => now(),
            'version' => '1.0.0'
        ]);
    });
});

// Protected API Routes
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);

    // === ROTAS DE PEDIDOS (PRINCIPAIS) ===
    Route::prefix('orders')->group(function () {
        // Listar pedidos do usuário
        Route::get('/', [OrderController::class, 'index']);

        // Criar novo pedido
        Route::post('/', [OrderController::class, 'store']);

        // Ver detalhes de um pedido específico
        Route::get('{order}', [OrderController::class, 'show']);

        // Cancelar pedido
        Route::patch('{order}/cancel', [OrderController::class, 'cancel']);

        // Rastrear pedido (para o app do cliente)
        Route::get('{order}/track', [OrderController::class, 'track']);

        // Atualizar status do pedido (para admin/restaurante)
        Route::put('{order}/status', [OrderController::class, 'updateStatus']);

        // === ROTAS DE PAGAMENTO POR PEDIDO ===
        Route::prefix('{order}/payment')->group(function () {
            // Iniciar pagamento M-Pesa
            Route::post('mpesa', [PaymentController::class, 'initiateMpesaPayment']);

            // Iniciar pagamento eMola
            Route::post('emola', [PaymentController::class, 'initiateMolaPayment']);

            // Confirmar pagamento em dinheiro
            Route::post('cash', [PaymentController::class, 'confirmCashPayment']);

            // Confirmar pagamento (genérico)
            Route::post('confirm', [PaymentController::class, 'confirmPayment']);

            // Verificar status do pagamento
            Route::get('status', [PaymentController::class, 'checkPaymentStatus']);
        });
    });

    // === ROTAS DE DELIVERY (PARA ENTREGADORES) ===
    Route::prefix('delivery')->group(function () {
        // Listar pedidos disponíveis para entrega
        Route::get('available-orders', [DeliveryController::class, 'availableOrders']);

        // Aceitar um pedido para entrega
        Route::post('orders/{order}/accept', [DeliveryController::class, 'acceptOrder']);

        // Listar minhas entregas
        Route::get('my-deliveries', [DeliveryController::class, 'myDeliveries']);

        // Atualizar status de entrega (com localização)
        Route::patch('orders/{order}/status', [DeliveryController::class, 'updateDeliveryStatus']);

        // Atualizar localização atual do entregador
        Route::post('location', [DeliveryController::class, 'updateLocation']);
    });

    // === ROTAS DE PAGAMENTO GERAIS ===
    Route::prefix('payment')->group(function () {
        // Listar métodos de pagamento disponíveis
        Route::get('methods', [PaymentController::class, 'getPaymentMethods']);

        // Histórico de pagamentos do usuário
        Route::get('history', [PaymentController::class, 'getPaymentHistory']);
    });

    // === ROTAS DE USUÁRIO ===
    Route::prefix('user')->group(function () {
        // Salvar token de push notification
        Route::post('save-push-token', [OrderController::class, 'savePushToken']);
        Route::post('push-token', [OrderController::class, 'savePushToken']); // Alias

        // Perfil do usuário
        Route::get('profile', [AuthController::class, 'getProfile']);
        Route::patch('profile', [AuthController::class, 'updateProfile']);

        // Endereços do usuário
        Route::get('addresses', [AuthController::class, 'getAddresses']);
        Route::post('addresses', [AuthController::class, 'storeAddress']);
        Route::put('addresses/{address}', [AuthController::class, 'updateAddress']);
        Route::delete('addresses/{address}', [AuthController::class, 'deleteAddress']);

        // Favoritos
        Route::get('favorites', [AuthController::class, 'getFavorites']);
        Route::post('favorites/{restaurant}', [AuthController::class, 'addToFavorites']);
        Route::delete('favorites/{restaurant}', [AuthController::class, 'removeFromFavorites']);
    });

    // === ROTAS DE NOTIFICAÇÕES ===
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::patch('{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{notification}', [NotificationController::class, 'destroy']);
    });

    // === ROTAS DE REVIEWS/AVALIAÇÕES ===
    Route::prefix('reviews')->group(function () {
        Route::post('/', [RestaurantController::class, 'storeReview']);
        Route::get('my-reviews', [RestaurantController::class, 'getUserReviews']);
    });
});

// === ROTAS PARA RESTAURANTES (OWNERS) ===
Route::middleware('auth:sanctum')->prefix('v1/restaurant')->group(function () {
    // Dashboard stats
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
                                                 ->whereIn('status', ['pending', 'confirmed'])
                                                 ->count(),
            'total_orders' => \App\Models\Order::where('restaurant_id', $restaurant->id)->count(),
            'average_rating' => $restaurant->reviews()->avg('rating') ?? 0,
            'total_reviews' => $restaurant->reviews()->count()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    });

    // Listar pedidos do restaurante
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

    // Listar menu do restaurante
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

// === WEBHOOKS PÚBLICOS (SEM AUTENTICAÇÃO) ===
// Webhook para pagamentos (M-Pesa, eMola, etc.)
Route::post('webhooks/payment', [PaymentController::class, 'paymentWebhook']);

// Webhook para notificações de terceiros
Route::post('webhooks/notifications', [NotificationController::class, 'webhook']);

// === ROTAS PARA ADMIN (se necessário) ===
Route::middleware(['auth:sanctum', 'admin'])->prefix('v1/admin')->group(function () {
    Route::get('dashboard/stats', [ReportsController::class, 'getDashboardStats']);
    Route::get('orders', function (Request $request) {
        return \App\Models\Order::with(['customer', 'restaurant', 'deliveryPerson'])
                                ->latest()
                                ->paginate($request->per_page ?? 20);
    });
    Route::get('users', function (Request $request) {
        return \App\Models\User::latest()->paginate($request->per_page ?? 20);
    });
});
