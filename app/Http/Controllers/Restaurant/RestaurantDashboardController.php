<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class RestaurantDashboardController extends Controller
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

    public function dashboard()
    {
        $restaurant = auth()->user()->restaurants()->first();

        if (!$restaurant) {
            return redirect()->route('restaurant.setup')
                           ->with('info', 'Você precisa configurar seu restaurante primeiro.');
        }

        $stats = [
            'orders_today' => Order::where('restaurant_id', $restaurant->id)
                                  ->whereDate('created_at', today())
                                  ->count(),
            'revenue_today' => Order::where('restaurant_id', $restaurant->id)
                                   ->whereDate('created_at', today())
                                   ->where('payment_status', 'paid')
                                   ->sum('total_amount'),
            'pending_orders' => Order::where('restaurant_id', $restaurant->id)
                                    ->where('status', 'pending')
                                    ->count(),
            'active_menu_items' => MenuItem::where('restaurant_id', $restaurant->id)
                                          ->where('is_available', true)
                                          ->count(),
        ];

        $recent_orders = Order::where('restaurant_id', $restaurant->id)
                             ->with(['customer', 'items.menuItem'])
                             ->latest()
                             ->take(10)
                             ->get();

        $popular_items = MenuItem::where('restaurant_id', $restaurant->id)
                               ->withCount(['orderItems' => function($query) {
                                   $query->whereHas('order', function($q) {
                                       $q->whereDate('created_at', '>=', now()->subDays(7));
                                   });
                               }])
                               ->orderBy('order_items_count', 'desc')
                               ->take(5)
                               ->get();

        return view('restaurant.dashboard', compact('restaurant', 'stats', 'recent_orders', 'popular_items'));
    }

    public function orders()
    {
        $restaurant = auth()->user()->restaurants()->first();

        $orders = Order::where('restaurant_id', $restaurant->id)
                      ->with(['customer', 'items.menuItem'])
                      ->latest()
                      ->paginate(20);

        return view('restaurant.orders.index', compact('orders', 'restaurant'));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $restaurant = auth()->user()->restaurants()->first();

        if ($order->restaurant_id !== $restaurant->id) {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,picked_up,delivered,cancelled'
        ]);

        $order->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Status do pedido atualizado com sucesso!',
            'status' => $order->status
        ]);
    }

    public function getOrderDetails($id)
{
    try {
        $order = Order::with(['customer', 'items.menuItem'])
            ->where('restaurant_id', auth()->user()->restaurant->id)
            ->findOrFail($id);

        $deliveryAddress = '';
        if (is_array($order->delivery_address)) {
            $addressParts = array_filter([
                $order->delivery_address['street'] ?? '',
                $order->delivery_address['city'] ?? ''
            ]);
            $deliveryAddress = implode(', ', $addressParts);
        } else {
            $deliveryAddress = $order->delivery_address ?? '';
        }

        $items = $order->items->map(function($item) {
            return [
                'name' => $item->menuItem->name ?? 'Item removido',
                'quantity' => $item->quantity,
                'unit_price' => number_format($item->unit_price, 2),
                'total_price' => number_format($item->total_price, 2),
                'special_instructions' => $item->special_instructions
            ];
        });

        return response()->json([
            'success' => true,
            'order' => [
                'order_number' => $order->order_number,
                'customer' => $order->customer->name,
                'customer_phone' => $order->customer->phone ?? '',
                'status' => $order->status,
                'total' => number_format($order->total_amount, 2),
                'created_at' => $order->created_at->format('d/m/Y H:i'),
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'delivery_address' => $deliveryAddress,
                'items' => $items
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Pedido não encontrado'
        ], 404);
    }
}
}
