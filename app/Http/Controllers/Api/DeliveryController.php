<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryController extends Controller
{
    /**
     * PEDIDOS DISPONÍVEIS PARA ENTREGA - Lógica do Uber Eats
     *
     * Mostra apenas pedidos:
     * 1. Status 'ready' (prontos para coleta)
     * 2. Sem entregador atribuído
     * 3. Próximos ao entregador (até 5km)
     * 4. Pagos e confirmados
     */
    public function availableOrders(Request $request)
    {
        $user = $request->user();

        // Verificar se é entregador
        if ($user->role !== 'delivery_person') {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado - Apenas entregadores podem acessar'
            ], 403);
        }

        try {
            // Obter localização atual do entregador
            $deliveryLat = $request->latitude;
            $deliveryLng = $request->longitude;

            if (!$deliveryLat || !$deliveryLng) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Localização obrigatória. Ative o GPS.'
                ], 400);
            }

            Log::info('Buscando pedidos para entregador', [
                'user_id' => $user->id,
                'location' => ['lat' => $deliveryLat, 'lng' => $deliveryLng]
            ]);

            // QUERY PRINCIPAL: Pedidos disponíveis próximos
            $orders = Order::with([
                'restaurant:id,name,phone,address,latitude,longitude,image,delivery_time_max',
                'customer:id,name,phone',
                'items.menuItem:id,name,price'
            ])
            ->select('orders.*')
            ->selectRaw('
                (6371 * acos(
                    cos(radians(?)) * cos(radians(restaurants.latitude)) *
                    cos(radians(restaurants.longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(restaurants.latitude))
                )) as distance_km
            ', [$deliveryLat, $deliveryLng, $deliveryLat])
            ->join('restaurants', 'orders.restaurant_id', '=', 'restaurants.id')
            ->where('orders.status', 'ready')                    // ✅ Prontos para coleta
            ->whereNull('orders.delivery_person_id')             // ✅ Sem entregador
            ->where('orders.payment_status', 'paid')             // ✅ Pagos
            ->whereNotNull('restaurants.latitude')               // ✅ Restaurante com GPS
            ->whereNotNull('restaurants.longitude')
            ->having('distance_km', '<=', 5)                     // ✅ Até 5km de distância
            ->orderBy('distance_km')                             // ✅ Mais próximos primeiro
            ->orderBy('orders.created_at')                       // ✅ Mais antigos depois
            ->paginate($request->per_page ?? 10);

            // Formatar dados para o app mobile
            $formattedOrders = $orders->getCollection()->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toISOString(),

                    // Valores financeiros
                    'total_amount' => (float) $order->total_amount,
                    'delivery_fee' => (float) ($order->delivery_fee ?? 50),
                    'payment_method' => $order->payment_method,

                    // Restaurante (ponto de coleta)
                    'restaurant' => [
                        'id' => $order->restaurant->id,
                        'name' => $order->restaurant->name,
                        'phone' => $order->restaurant->phone,
                        'address' => $order->restaurant->address,
                        'latitude' => (float) $order->restaurant->latitude,
                        'longitude' => (float) $order->restaurant->longitude,
                        'image' => $order->restaurant->image,
                        'estimated_prep_time' => $order->restaurant->delivery_time_max ?? 30
                    ],

                    // Cliente (destino da entrega)
                    'customer' => [
                        'name' => $order->customer->name ?? 'Cliente',
                        'phone' => $order->customer->phone
                    ],

                    // Endereço de entrega
                    'delivery_address' => $order->delivery_address,

                    // Itens do pedido (para conferência)
                    'items' => $order->items->map(function($item) {
                        return [
                            'quantity' => $item->quantity,
                            'name' => $item->menuItem->name ?? 'Item',
                            'price' => (float) $item->unit_price
                        ];
                    }),

                    // Distância calculada
                    'distance_km' => round($order->distance_km, 2),
                    'estimated_distance_text' => $this->formatDistance($order->distance_km),

                    // Tempo estimado total
                    'estimated_total_time' => $this->calculateTotalTime($order->distance_km),

                    // Observações
                    'notes' => $order->notes
                ];
            });

            $response = $orders->toArray();
            $response['data'] = $formattedOrders;

            return response()->json([
                'status' => 'success',
                'message' => "Encontrados {$orders->total()} pedidos próximos",
                'data' => $response,
                'delivery_info' => [
                    'current_location' => [
                        'latitude' => (float) $deliveryLat,
                        'longitude' => (float) $deliveryLng
                    ],
                    'search_radius_km' => 5,
                    'total_available' => $orders->total()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar pedidos disponíveis', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar pedidos disponíveis',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * ACEITAR PEDIDO - Como o Uber Eats
     */
    public function acceptOrder(Order $order, Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'delivery_person') {
            return response()->json([
                'status' => 'error',
                'message' => 'Apenas entregadores podem aceitar pedidos'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Verificar se pedido ainda está disponível
            if ($order->status !== 'ready' || $order->delivery_person_id !== null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Este pedido já foi aceito por outro entregador'
                ], 400);
            }

            // Verificar se entregador já tem entrega ativa
            $activeDelivery = Order::where('delivery_person_id', $user->id)
                                  ->whereIn('status', ['picked_up', 'on_way'])
                                  ->first();

            if ($activeDelivery) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Você já possui uma entrega ativa. Complete-a antes de aceitar outra.'
                ], 400);
            }

            // ACEITAR O PEDIDO
            $order->update([
                'delivery_person_id' => $user->id,
                'status' => 'on_way',  // Status: "A caminho do restaurante"
                'picked_up_at' => null, // Será preenchido quando coletar
                'accepted_at' => now()
            ]);

            // Carregar dados completos
            $order->load([
                'restaurant:id,name,phone,address,latitude,longitude,image',
                'customer:id,name,phone',
                'items.menuItem:id,name,price'
            ]);

            DB::commit();

            Log::info('Pedido aceito por entregador', [
                'order_id' => $order->id,
                'delivery_person_id' => $user->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pedido aceito com sucesso! Vá buscar no restaurante.',
                'data' => [
                    'order' => $this->formatOrderForDelivery($order),
                    'next_action' => 'go_to_restaurant',
                    'restaurant_location' => [
                        'latitude' => (float) $order->restaurant->latitude,
                        'longitude' => (float) $order->restaurant->longitude,
                        'address' => $order->restaurant->address
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao aceitar pedido', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao aceitar pedido'
            ], 500);
        }
    }

    /**
     * MINHAS ENTREGAS ATIVAS
     */
    public function myDeliveries(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'delivery_person') {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        try {
            // Buscar entregas ativas e histórico recente
            $deliveries = Order::with([
                'restaurant:id,name,phone,address,latitude,longitude,image',
                'customer:id,name,phone',
                'items.menuItem:id,name'
            ])
            ->where('delivery_person_id', $user->id)
            ->whereIn('status', ['on_way', 'picked_up', 'delivered']) // Estados relevantes
            ->latest()
            ->paginate($request->per_page ?? 15);

            $formattedDeliveries = $deliveries->getCollection()->map(function($order) {
                return $this->formatOrderForDelivery($order);
            });

            $response = $deliveries->toArray();
            $response['data'] = $formattedDeliveries;

            // Estatísticas do entregador
            $stats = [
                'active_deliveries' => Order::where('delivery_person_id', $user->id)
                                           ->whereIn('status', ['on_way', 'picked_up'])
                                           ->count(),
                'completed_today' => Order::where('delivery_person_id', $user->id)
                                         ->where('status', 'delivered')
                                         ->whereDate('delivered_at', today())
                                         ->count(),
                'total_earnings_today' => Order::where('delivery_person_id', $user->id)
                                               ->where('status', 'delivered')
                                               ->whereDate('delivered_at', today())
                                               ->sum('delivery_fee')
            ];

            return response()->json([
                'status' => 'success',
                'data' => $response,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar entregas do entregador', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar suas entregas'
            ], 500);
        }
    }

    /**
     * ATUALIZAR STATUS DE ENTREGA + LOCALIZAÇÃO
     */
    public function updateDeliveryStatus(Order $order, Request $request)
    {
        $user = $request->user();

        if ($order->delivery_person_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Este pedido não pertence a você'
            ], 403);
        }

        $validStatuses = ['on_way', 'picked_up', 'delivered'];

        if (!in_array($request->status, $validStatuses)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Status inválido'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $updateData = ['status' => $request->status];

            // Atualizar timestamps baseado no status
            switch ($request->status) {
                case 'picked_up':
                    $updateData['picked_up_at'] = now();
                    break;
                case 'delivered':
                    $updateData['delivered_at'] = now();
                    $updateData['payment_status'] = 'paid'; // Confirmar pagamento
                    break;
            }

            // Atualizar localização do entregador se fornecida
            if ($request->has('latitude') && $request->has('longitude')) {
                $updateData['delivery_latitude'] = $request->latitude;
                $updateData['delivery_longitude'] = $request->longitude;
                $updateData['location_updated_at'] = now();
            }

            $order->update($updateData);

            DB::commit();

            Log::info('Status de entrega atualizado', [
                'order_id' => $order->id,
                'old_status' => $order->getOriginal('status'),
                'new_status' => $request->status,
                'delivery_person_id' => $user->id
            ]);

            $message = $this->getStatusUpdateMessage($request->status);

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'order' => $this->formatOrderForDelivery($order->fresh()),
                    'next_action' => $this->getNextAction($request->status)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao atualizar status da entrega', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao atualizar status'
            ], 500);
        }
    }

    /**
     * ATUALIZAR LOCALIZAÇÃO EM TEMPO REAL
     */
    public function updateLocation(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'delivery_person') {
            return response()->json([
                'status' => 'error',
                'message' => 'Apenas entregadores podem atualizar localização'
            ], 403);
        }

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        try {
            // Atualizar localização do usuário
            $user->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_updated_at' => now()
            ]);

            // Atualizar também nos pedidos ativos
            Order::where('delivery_person_id', $user->id)
                 ->whereIn('status', ['on_way', 'picked_up'])
                 ->update([
                     'delivery_latitude' => $request->latitude,
                     'delivery_longitude' => $request->longitude,
                     'location_updated_at' => now()
                 ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Localização atualizada'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao atualizar localização'
            ], 500);
        }
    }

    // === MÉTODOS AUXILIARES ===

    private function formatOrderForDelivery($order)
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total_amount' => (float) $order->total_amount,
            'delivery_fee' => (float) ($order->delivery_fee ?? 50),
            'payment_method' => $order->payment_method,
            'created_at' => $order->created_at->toISOString(),
            'picked_up_at' => $order->picked_up_at?->toISOString(),
            'delivered_at' => $order->delivered_at?->toISOString(),

            'restaurant' => [
                'name' => $order->restaurant->name,
                'phone' => $order->restaurant->phone,
                'address' => $order->restaurant->address,
                'latitude' => (float) $order->restaurant->latitude,
                'longitude' => (float) $order->restaurant->longitude,
            ],

            'customer' => [
                'name' => $order->customer->name ?? 'Cliente',
                'phone' => $order->customer->phone,
            ],

            'delivery_address' => $order->delivery_address,

            'items' => $order->items->map(fn($item) => [
                'quantity' => $item->quantity,
                'name' => $item->menuItem->name ?? 'Item'
            ]),

            'notes' => $order->notes
        ];
    }

    private function formatDistance($km)
    {
        if ($km < 1) {
            return round($km * 1000) . 'm';
        }
        return round($km, 1) . 'km';
    }

    private function calculateTotalTime($distanceKm)
    {
        // Tempo médio: 5 min de coleta + 2 min por km + 3 min de entrega
        $pickupTime = 5;
        $travelTime = $distanceKm * 2;
        $deliveryTime = 3;

        return round($pickupTime + $travelTime + $deliveryTime) . ' min';
    }

    private function getStatusUpdateMessage($status)
    {
        $messages = [
            'on_way' => 'A caminho do restaurante',
            'picked_up' => 'Pedido coletado! Agora vá entregar ao cliente.',
            'delivered' => 'Entrega concluída com sucesso!'
        ];

        return $messages[$status] ?? 'Status atualizado';
    }

    private function getNextAction($status)
    {
        $actions = [
            'on_way' => 'collect_order',
            'picked_up' => 'deliver_order',
            'delivered' => 'complete'
        ];

        return $actions[$status] ?? null;
    }
}
