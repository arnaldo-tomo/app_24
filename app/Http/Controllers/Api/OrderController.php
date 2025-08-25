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
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Lista todos os pedidos do usuário autenticado
     * GET /api/v1/orders
     */
    public function index(Request $request)
    {
        try {
            $orders = Order::with([
                'restaurant:id,name,image,delivery_time_min,delivery_time_max',
                'items.menuItem:id,name,price',
                'deliveryPerson:id,name,phone'
            ])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->per_page ?? 15);

            return response()->json([
                'status' => 'success',
                'data' => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar pedidos',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Cria um novo pedido
     * POST /api/v1/orders
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.customizations' => 'sometimes|array',
            'delivery_address.street' => 'required|string',
            'delivery_address.city' => 'sometimes|string',
            'delivery_address.latitude' => 'sometimes|numeric',
            'delivery_address.longitude' => 'sometimes|numeric',
            'payment_method' => 'required|in:cash,mpesa,emola',
            'notes' => 'sometimes|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Validar restaurante
            $restaurant = Restaurant::findOrFail($request->restaurant_id);
            if (!$restaurant->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Restaurante não disponível'
                ], 400);
            }

            // Calcular totais
            $subtotal = 0;
            $items = [];

            foreach ($request->items as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);

                if (!$menuItem->is_available) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Item '{$menuItem->name}' não está disponível"
                    ], 400);
                }

                $itemTotal = $menuItem->price * $item['quantity'];
                $subtotal += $itemTotal;

                $items[] = [
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'price' => $menuItem->price,
                    'total' => $itemTotal,
                    'customizations' => $item['customizations'] ?? null
                ];
            }

            // Calcular valores
            $deliveryFee = $restaurant->delivery_fee ?? 50.00;
            $taxAmount = 0; // Implementar cálculo de taxa se necessário
            $discountAmount = 0; // Implementar desconto se necessário
            $totalAmount = $subtotal + $deliveryFee + $taxAmount - $discountAmount;

            // Criar pedido
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $request->user()->id,
                'restaurant_id' => $request->restaurant_id,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'delivery_address' => $request->delivery_address,
                'notes' => $request->notes
            ]);

            // Criar itens do pedido
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['total'],
                    'customizations' => $item['customizations']
                ]);
            }

            // Carregar relacionamentos
            $order->load(['restaurant', 'items.menuItem', 'deliveryPerson']);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pedido criado com sucesso',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao criar pedido',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 400);
        }
    }

    /**
     * Mostra detalhes de um pedido específico
     * GET /api/v1/orders/{order}
     */
    public function show(Order $order, Request $request)
    {
        // Verificar se o pedido pertence ao usuário
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        // Carregar relacionamentos
        $order->load([
            'restaurant:id,name,image,phone,delivery_time_min,delivery_time_max',
            'items.menuItem:id,name,price,image',
            'deliveryPerson:id,name,phone'
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    /**
     * Cancela um pedido
     * PATCH /api/v1/orders/{order}/cancel
     */
    public function cancel(Order $order, Request $request)
    {
        // Verificar se o pedido pertence ao usuário
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        // Verificar se pode ser cancelado
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pedido não pode ser cancelado'
            ], 400);
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $request->reason ?? 'Cancelado pelo cliente'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pedido cancelado com sucesso'
        ]);
    }

    /**
     * Rastreia um pedido (informações de entrega)
     * GET /api/v1/orders/{order}/track
     */
    public function track(Order $order, Request $request)
    {
        // Verificar se o pedido pertence ao usuário
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        // Carregar dados necessários para rastreamento
        $order->load([
            'restaurant:id,name,phone,address,latitude,longitude',
            'deliveryPerson:id,name,phone',
            'items.menuItem:id,name'
        ]);

        // Informações de rastreamento
        $trackingInfo = [
            'order' => $order,
            'status_history' => [
                [
                    'status' => 'pending',
                    'label' => 'Pedido Recebido',
                    'completed' => true,
                    'timestamp' => $order->created_at
                ],
                [
                    'status' => 'confirmed',
                    'label' => 'Confirmado',
                    'completed' => in_array($order->status, ['confirmed', 'preparing', 'ready', 'picked_up', 'delivered']),
                    'timestamp' => $order->confirmed_at
                ],
                [
                    'status' => 'preparing',
                    'label' => 'Preparando',
                    'completed' => in_array($order->status, ['preparing', 'ready', 'picked_up', 'delivered']),
                    'timestamp' => $order->preparing_at
                ],
                [
                    'status' => 'ready',
                    'label' => 'Pronto',
                    'completed' => in_array($order->status, ['ready', 'picked_up', 'delivered']),
                    'timestamp' => $order->ready_at
                ],
                [
                    'status' => 'picked_up',
                    'label' => 'Saiu para Entrega',
                    'completed' => in_array($order->status, ['picked_up', 'delivered']),
                    'timestamp' => $order->picked_up_at
                ],
                [
                    'status' => 'delivered',
                    'label' => 'Entregue',
                    'completed' => $order->status === 'delivered',
                    'timestamp' => $order->delivered_at
                ]
            ],
            'estimated_delivery_time' => $order->estimated_delivery_time,
            'current_location' => $order->deliveryPerson ? [
                'latitude' => $order->delivery_latitude,
                'longitude' => $order->delivery_longitude,
                'last_updated' => $order->location_updated_at
            ] : null
        ];

        return response()->json([
            'status' => 'success',
            'data' => $trackingInfo
        ]);
    }

    /**
     * Atualiza status do pedido (para admin/restaurante)
     * PUT /api/v1/orders/{order}/status
     */
    public function updateStatus(Order $order, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,preparing,ready,picked_up,delivered,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        $previousStatus = $order->status;
        $newStatus = $request->status;

        // Atualizar campos de timestamp baseado no status
        $updateData = ['status' => $newStatus];

        switch ($newStatus) {
            case 'confirmed':
                $updateData['confirmed_at'] = now();
                break;
            case 'preparing':
                $updateData['preparing_at'] = now();
                break;
            case 'ready':
                $updateData['ready_at'] = now();
                break;
            case 'picked_up':
                $updateData['picked_up_at'] = now();
                break;
            case 'delivered':
                $updateData['delivered_at'] = now();
                $updateData['payment_status'] = 'paid'; // Marcar como pago quando entregue
                break;
        }

        $order->update($updateData);

        // Enviar notificação push (se implementado)
        try {
            $this->sendStatusUpdateNotification($order, $previousStatus, $newStatus);
        } catch (\Exception $e) {
            // Log do erro mas não falhar a requisição
            \Log::error('Erro ao enviar notificação: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Status atualizado com sucesso',
            'data' => $order->fresh()
        ]);
    }

    /**
     * Salva token de push notification
     * POST /api/v1/user/save-push-token
     */
    public function savePushToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'push_token' => 'required|string',
            'platform' => 'required|in:ios,android'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->update([
            'push_token' => $request->push_token,
            'platform' => $request->platform
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Token salvo com sucesso'
        ]);
    }

    /**
     * Método privado para enviar notificações de status
     */
    private function sendStatusUpdateNotification($order, $previousStatus, $newStatus)
    {
        if (!$order->user || !$order->user->push_token) {
            return;
        }

        $statusMessages = [
            'confirmed' => 'Seu pedido foi confirmado!',
            'preparing' => 'Seu pedido está sendo preparado',
            'ready' => 'Seu pedido está pronto!',
            'picked_up' => 'Seu pedido saiu para entrega',
            'delivered' => 'Seu pedido foi entregue!'
        ];

        $message = $statusMessages[$newStatus] ?? 'Status do pedido atualizado';

        // Implementar envio de push notification aqui
        // Exemplo: SimplePushService::send($order->user->push_token, $message);
    }
}
