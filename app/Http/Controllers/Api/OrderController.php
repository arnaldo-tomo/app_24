<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Services\PushService;
use App\Services\SimplePushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['restaurant', 'items.menuItem', 'deliveryPerson'])
                      ->where('user_id', $request->user()->id)
                      ->latest()
                      ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.customizations' => 'sometimes|array',
            // 'items.*.special_instructions' => 'sometimes|string|max:500',
            'delivery_address' => 'required|array',
            'delivery_address.street' => 'required|string',
            'delivery_address.city' => 'required|string',
            'delivery_address.latitude' => 'required|numeric|between:-90,90',
            'delivery_address.longitude' => 'required|numeric|between:-180,180',
            'payment_method' => 'required|in:mpesa,mola,cash',
            'notes' => 'sometimes|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados de validação inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $restaurant = Restaurant::findOrFail($request->restaurant_id);

        if (!$restaurant->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Restaurante não está disponível'
            ], 400);
        }

        if (!$restaurant->isOpen()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Restaurante está fechado'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $subtotal = 0;
            $orderItems = [];

            // Calculate subtotal and prepare order items
            foreach ($request->items as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);

                if ($menuItem->restaurant_id !== $restaurant->id) {
                    throw new \Exception('Item não pertence ao restaurante selecionado');
                }

                if (!$menuItem->is_available) {
                    throw new \Exception("Item '{$menuItem->name}' não está disponível");
                }

                $unitPrice = $menuItem->getFinalPrice();
                $totalPrice = $unitPrice * $item['quantity'];
                $subtotal += $totalPrice;

                $orderItems[] = [
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'customizations' => $item['customizations'] ?? null,
                    'special_instructions' => $item['special_instructions'] ?? null,
                ];
            }

            // Check minimum order amount
            if ($subtotal < $restaurant->minimum_order) {
                throw new \Exception("Pedido mínimo é de MT {$restaurant->minimum_order}");
            }

            // Calculate fees and total
            $deliveryFee = $restaurant->delivery_fee;
            $taxAmount = $subtotal * 0.17; // 17% IVA
            $totalAmount = $subtotal + $deliveryFee + $taxAmount;

            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'restaurant_id' => $restaurant->id,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'delivery_address' => $request->delivery_address,
                'notes' => $request->notes,
                'estimated_delivery_time' => now()->addMinutes($restaurant->getAverageDeliveryTime()),
            ]);

            // Create order items
            foreach ($orderItems as $orderItem) {
                $orderItem['order_id'] = $order->id;
                OrderItem::create($orderItem);
            }

            DB::commit();

            $order->load(['restaurant', 'items.menuItem']);

 // Se método de pagamento for dinheiro, confirmar automaticamente
 if ($request->payment_method === 'cash') {
    $order->update([
        'status' => 'confirmed', // Pedido confirmado mesmo com pagamento pendente
        'payment_status' => 'pending' // Será pago na entrega
    ]);
}

            return response()->json([
                'status' => 'success',
                'message' => 'Pedido criado com sucesso',
                'data' => [
                    'order' => $order
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        $order->load(['restaurant', 'items.menuItem', 'deliveryPerson']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'order' => $order
            ]
        ]);
    }

    public function cancel(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        if (!$order->canBeCancelled()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pedido não pode ser cancelado'
            ], 400);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json([
            'status' => 'success',
            'message' => 'Pedido cancelado com sucesso'
        ]);
    }

    public function track(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        $order->load(['restaurant', 'deliveryPerson']);

        $tracking = [
            'order_number' => $order->order_number,
            'status' => $order->status,
            'estimated_delivery_time' => $order->estimated_delivery_time,
            'restaurant' => [
                'name' => $order->restaurant->name,
                'phone' => $order->restaurant->phone,
                'address' => $order->restaurant->address,
            ]
        ];

        if ($order->deliveryPerson) {
            $tracking['delivery_person'] = [
                'name' => $order->deliveryPerson->name,
                'phone' => $order->deliveryPerson->phone,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $tracking
        ]);
    }

    public function savePushToken(Request $request)
    {
        auth()->user()->update([
            'push_token' => $request->push_token
        ]);

        return response()->json(['status' => 'success']);
    }

    public function updateStatus(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        $oldStatus = $order->status;

        $order->update(['status' => $request->status]);

        // Enviar notificação se status mudou
        if ($oldStatus !== $request->status) {
            $pushService = new PushService();
            $pushService->sendOrderUpdate($order->user, $orderId, $request->status);
        }

        return response()->json(['status' => 'success']);
    }



}
