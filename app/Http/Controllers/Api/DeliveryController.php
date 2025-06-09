<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{


    /**
     * ENTREGAS DISPONÍVEIS - Como iFood
     *
     * Regra: Mostrar apenas pedidos que estão PRONTOS para coleta
     * Status 'ready' = Restaurante terminou de preparar e está esperando entregador
     * Sem delivery_person_id = Ainda não foi aceito por nenhum entregador
     */
    public function availableOrders(Request $request)
    {
        $user = $request->user();

        // Verificar se é entregador
        if (!$user->isDeliveryPerson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado - Apenas entregadores podem ver entregas disponíveis'
            ], 403);
        }

        try {
            \Log::info('Buscando pedidos disponíveis para entregador: ' . $user->id);

            // LÓGICA IGUAL AO IFOOD:
            // 1. Status = 'ready' (pedido pronto para coleta)
            // 2. Sem entregador atribuído (delivery_person_id = null)
            // 3. Ordenar por data de criação (mais antigos primeiro)
            $orders = Order::with([
                // 'restaurant:id,name,phone,address,latitude,longitude,image',
                'customer:id,name,phone,email,address',
                // 'items:id,order_id,menu_item_id,quantity,price,notes',
            ])
            ->where('status', 'ready')           // ← Pedido PRONTO para coleta
            // ->whereNull('delivery_person_id')    // ← SEM entregador atribuído
            // ->where('payment_status', 'paid')    // ← Apenas pedidos PAGOS (opcional)
            // ->orderBy('created_at', 'asc')       // ← Mais antigos primeiro
            ->paginate($request->per_page ?? 15);

            \Log::info('Encontrados ' . $orders->total() . ' pedidos disponíveis');

            // Formatar dados para o app
            $formattedOrders = $orders->getCollection()->map(function($order) {
                return $this->formatOrderForMobile($order);
            });

            $response = $orders->toArray();
            $response['data'] = $formattedOrders;

            return response()->json([
                'status' => 'success',
                'message' => 'Pedidos disponíveis carregados com sucesso',
                'data' => $response
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar pedidos disponíveis: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar pedidos disponíveis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ACEITAR PEDIDO - Como iFood
     *
     * Quando entregador aceita:
     * 1. Atribui delivery_person_id
     * 2. Muda status para 'picked_up' (saiu para coleta)
     */
    public function acceptOrder(Request $request, Order $order)
    {
        $user = $request->user();

        if (!$user->isDeliveryPerson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        // Verificar se pedido ainda está disponível
        if ($order->status !== 'ready' || $order->delivery_person_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pedido não está mais disponível para entrega'
            ], 400);
        }

        try {
            \Log::info("Entregador {$user->id} aceitando pedido {$order->id}");

            $order->update([
                'delivery_person_id' => $user->id,
                'status' => 'picked_up'    // ← Entregador saiu para coleta
            ]);

            // Recarregar com relacionamentos
            $order = $order->fresh([
                'restaurant:id,name,phone,address,latitude,longitude,image',
                'customer:id,name,phone,email,address',
                'items:id,order_id,menu_item_id,quantity,price,notes',
                'items.menuItem:id,name,description,price,image'
            ]);

            \Log::info("Pedido {$order->order_number} aceito com sucesso");

            return response()->json([
                'status' => 'success',
                'message' => 'Pedido aceito para entrega',
                'data' => [
                    'order' => $this->formatOrderForMobile($order)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao aceitar pedido: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao aceitar pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * MINHAS ENTREGAS - Histórico do entregador
     */
    public function myDeliveries(Request $request)
    {
        $user = $request->user();

        if (!$user->isDeliveryPerson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        try {
            $orders = Order::with([
                'restaurant:id,name,phone,address,latitude,longitude,image',
                'customer:id,name,phone,email,address',
                'items:id,order_id,menu_item_id,quantity,price,notes',
                'items.menuItem:id,name,description,price,image'
            ])
            ->where('delivery_person_id', $user->id)  // ← Apenas pedidos DESTE entregador
            ->orderBy('created_at', 'desc')           // ← Mais recentes primeiro
            ->paginate($request->per_page ?? 15);

            \Log::info('Entregador ' . $user->id . ' tem ' . $orders->total() . ' entregas');

            $formattedOrders = $orders->getCollection()->map(function($order) {
                return $this->formatOrderForMobile($order);
            });

            $response = $orders->toArray();
            $response['data'] = $formattedOrders;

            return response()->json([
                'status' => 'success',
                'message' => 'Suas entregas carregadas com sucesso',
                'data' => $response
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar entregas do entregador: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar suas entregas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ATUALIZAR STATUS - Fluxo da entrega
     *
     * Fluxo completo:
     * ready → picked_up → delivered
     */
    public function updateDeliveryStatus(Request $request, Order $order)
    {
        $user = $request->user();

        if (!$user->isDeliveryPerson() || $order->delivery_person_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado - Este pedido não é seu'
            ], 403);
        }

        $validator = \Validator::make($request->all(), [
            'status' => 'required|in:picked_up,delivered',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = ['status' => $request->status];

            if ($request->status === 'delivered') {
                $updateData['delivered_at'] = now();
                \Log::info("Pedido {$order->order_number} marcado como entregue");
            }

            $order->update($updateData);

            // Atualizar localização do entregador
            if ($request->has('latitude') && $request->has('longitude')) {
                $user->update([
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude
                ]);
            }

            $order = $order->fresh([
                'restaurant:id,name,phone,address,latitude,longitude,image',
                'customer:id,name,phone,email,address',
                'items:id,order_id,menu_item_id,quantity,price,notes',
                'items.menuItem:id,name,description,price,image'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Status da entrega atualizado',
                'data' => [
                    'order' => $this->formatOrderForMobile($order)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao atualizar status da entrega',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatar pedido para o app mobile
     */
    private function formatOrderForMobile($order)
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total_amount' => number_format($order->total_amount, 2, '.', ''),
            'subtotal' => number_format($order->subtotal, 2, '.', ''),
            'delivery_fee' => number_format($order->delivery_fee, 2, '.', ''),
            'tax_amount' => number_format($order->tax_amount ?? 0, 2, '.', ''),
            'discount_amount' => number_format($order->discount_amount ?? 0, 2, '.', ''),
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'delivery_address' => $order->delivery_address,
            'notes' => $order->notes,
            'estimated_delivery_time' => $order->estimated_delivery_time,
            'delivered_at' => $order->delivered_at?->toISOString(),
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),

            'restaurant' => $order->restaurant ? [
                'id' => $order->restaurant->id,
                'name' => $order->restaurant->name,
                'phone' => $order->restaurant->phone,
                'address' => $order->restaurant->address,
                'latitude' => $order->restaurant->latitude,
                'longitude' => $order->restaurant->longitude,
                'image' => $order->restaurant->image,
            ] : null,

            'customer' => $order->customer ? [
                'id' => $order->customer->id,
                'name' => $order->customer->name,
                'phone' => $order->customer->phone,
                'email' => $order->customer->email,
                'address' => $order->customer->address,
            ] : null,

            'items' => $order->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'price' => number_format($item->price, 2, '.', ''),
                    'notes' => $item->notes,
                    'menu_item' => $item->menuItem ? [
                        'id' => $item->menuItem->id,
                        'name' => $item->menuItem->name,
                        'description' => $item->menuItem->description,
                        'price' => number_format($item->menuItem->price, 2, '.', ''),
                        'image' => $item->menuItem->image,
                    ] : null
                ];
            })->toArray(),
        ];
    }
}