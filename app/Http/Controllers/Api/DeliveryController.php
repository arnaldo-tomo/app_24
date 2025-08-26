<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DeliveryController extends Controller
{
    /**
     * ENTREGAS DISPONÍVEIS - Com filtro de proximidade
     *
     * ✅ CORREÇÃO: Agora filtra pedidos baseado na localização do entregador
     *
     * Regras:
     * 1. Status = 'ready' (pedido pronto para coleta)
     * 2. Sem entregador atribuído (delivery_person_id = null)
     * 3. ✅ NOVO: Dentro do raio de entrega do entregador
     * 4. Ordenar por distância (mais próximos primeiro)
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
            Log::info('Buscando pedidos disponíveis para entregador: ' . $user->id);
            Log::info('Localização do entregador:', [
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
                'raio' => $user->delivery_radius_km ?? 5
            ]);

            // ✅ VERIFICAR SE ENTREGADOR TEM LOCALIZAÇÃO
            if (!$user->latitude || !$user->longitude) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Por favor, atualize sua localização para ver pedidos disponíveis',
                    'data' => [
                        'orders' => [],
                        'total' => 0,
                        'requires_location' => true
                    ]
                ]);
            }

            $deliveryPersonLat = $user->latitude;
            $deliveryPersonLng = $user->longitude;
            $deliveryRadius = $user->delivery_radius_km ?? 5; // Padrão: 5km

            // ✅ QUERY COM FILTRO DE DISTÂNCIA
            $orders = Order::with([
                'restaurant:id,name,phone,address,latitude,longitude,image',
                'customer:id,name,phone,email,address',
                'items:id,order_id,menu_item_id,quantity,price,notes',
            ])
            ->where('status', 'ready')           // Pedido PRONTO para coleta
            ->whereNull('delivery_person_id')    // SEM entregador atribuído
            ->where('payment_status', 'paid')    // Apenas pedidos PAGOS

            // ✅ FILTRO DE PROXIMIDADE - Fórmula de Haversine
            // Calcular distância do restaurante até o entregador
            ->whereHas('restaurant', function($query) use ($deliveryPersonLat, $deliveryPersonLng, $deliveryRadius) {
                $query->selectRaw("
                    restaurants.*,
                    (6371 * acos(
                        cos(radians(?)) *
                        cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(latitude))
                    )) AS restaurant_distance
                ", [$deliveryPersonLat, $deliveryPersonLng, $deliveryPersonLat])
                ->havingRaw('restaurant_distance <= ?', [$deliveryRadius]);
            })

            // ✅ ADICIONAR CÁLCULO DE DISTÂNCIA NA QUERY PRINCIPAL
            ->selectRaw("
                orders.*,
                (6371 * acos(
                    cos(radians(?)) *
                    cos(radians((SELECT latitude FROM restaurants WHERE restaurants.id = orders.restaurant_id))) *
                    cos(radians((SELECT longitude FROM restaurants WHERE restaurants.id = orders.restaurant_id)) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians((SELECT latitude FROM restaurants WHERE restaurants.id = orders.restaurant_id)))
                )) AS distance_km
            ", [$deliveryPersonLat, $deliveryPersonLng, $deliveryPersonLat])

            ->orderBy('distance_km', 'asc')      // Mais próximos primeiro
            ->orderBy('created_at', 'asc')       // Em caso de empate, mais antigos primeiro
            ->paginate($request->per_page ?? 15);

            Log::info('Encontrados ' . $orders->total() . ' pedidos dentro do raio de ' . $deliveryRadius . 'km');

            // ✅ FORMATAR DADOS COM INFORMAÇÃO DE DISTÂNCIA
            $formattedOrders = $orders->getCollection()->map(function($order) use ($deliveryPersonLat, $deliveryPersonLng) {
                $formattedOrder = $this->formatOrderForMobile($order);

                // Adicionar informações de distância
                $formattedOrder['distance_km'] = round($order->distance_km ?? 0, 2);
                $formattedOrder['estimated_pickup_time'] = $this->calculateEstimatedTime($order->distance_km ?? 0);

                return $formattedOrder;
            });

            $response = $orders->toArray();
            $response['data'] = $formattedOrders;

            // ✅ ADICIONAR INFORMAÇÕES DE DEBUG
            $response['debug_info'] = [
                'delivery_person_location' => [
                    'latitude' => $deliveryPersonLat,
                    'longitude' => $deliveryPersonLng,
                ],
                'delivery_radius_km' => $deliveryRadius,
                'total_orders_found' => $orders->total(),
            ];

            return response()->json([
                'status' => 'success',
                'message' => "Encontrados {$orders->total()} pedidos num raio de {$deliveryRadius}km",
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar pedidos disponíveis: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar pedidos disponíveis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ MÉTODO AUXILIAR: Calcular tempo estimado baseado na distância
     */
    private function calculateEstimatedTime($distanceKm)
    {
        if ($distanceKm <= 0) return '5 min';

        // Velocidade média em cidade: 20 km/h (incluindo trânsito)
        $timeHours = $distanceKm / 20;
        $timeMinutes = ceil($timeHours * 60);

        // Mínimo 5 minutos, máximo 60 minutos
        $timeMinutes = max(5, min(60, $timeMinutes));

        return $timeMinutes . ' min';
    }

    /**
     * ACEITAR PEDIDO - Como iFood
     */
    public function acceptOrder(Request $request, $orderId)
    {
        $user = $request->user();

        if (!$user->isDeliveryPerson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        try {
            $order = Order::with(['restaurant', 'customer'])
                          ->where('id', $orderId)
                          ->where('status', 'ready')
                          ->whereNull('delivery_person_id')
                          ->first();

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pedido não disponível ou já foi aceito por outro entregador'
                ], 404);
            }

            // ✅ VERIFICAR SE ESTÁ DENTRO DO RAIO ANTES DE ACEITAR
            if ($user->latitude && $user->longitude && $order->restaurant) {
                $distance = $this->calculateDistance(
                    $user->latitude,
                    $user->longitude,
                    $order->restaurant->latitude,
                    $order->restaurant->longitude
                );

                $maxDistance = $user->delivery_radius_km ?? 5;

                if ($distance > $maxDistance) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Pedido fora do seu raio de entrega ({$maxDistance}km). Distância: {$distance}km"
                    ], 400);
                }
            }

            // Atribuir entregador e mudar status
            $order->update([
                'delivery_person_id' => $user->id,
                'status' => 'accepted',
                'accepted_at' => now()
            ]);

            Log::info("Pedido {$orderId} aceito pelo entregador {$user->id}");

            return response()->json([
                'status' => 'success',
                'message' => 'Pedido aceito com sucesso!',
                'data' => [
                    'order' => $this->formatOrderForMobile($order->fresh(['restaurant', 'customer']))
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao aceitar pedido: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao aceitar pedido'
            ], 500);
        }
    }

    /**
     * ✅ MÉTODO AUXILIAR: Calcular distância entre dois pontos (Haversine)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Raio da Terra em km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * MINHAS ENTREGAS
     */
    public function myDeliveries(Request $request)
    {
        $user = $request->user();

        $orders = Order::with(['restaurant', 'customer'])
                      ->where('delivery_person_id', $user->id)
                      ->orderBy('created_at', 'desc')
                      ->paginate($request->per_page ?? 15);

        $formattedOrders = $orders->getCollection()->map(function($order) {
            return $this->formatOrderForMobile($order);
        });

        $response = $orders->toArray();
        $response['data'] = $formattedOrders;

        return response()->json([
            'status' => 'success',
            'data' => $response
        ]);
    }

    /**
     * ATUALIZAR STATUS DE ENTREGA
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        $user = $request->user();

        $request->validate([
            'status' => 'required|in:picked_up,on_the_way,delivered',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
        ]);

        try {
            $order = Order::where('id', $orderId)
                         ->where('delivery_person_id', $user->id)
                         ->first();

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pedido não encontrado'
                ], 404);
            }

            $order->update([
                'status' => $request->status,
                $request->status . '_at' => now()
            ]);

            // Atualizar localização do entregador se fornecida
            if ($request->has('latitude') && $request->has('longitude')) {
                $user->update([
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'last_location_update' => now()
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Status atualizado com sucesso',
                'data' => [
                    'order' => $this->formatOrderForMobile($order->fresh(['restaurant', 'customer']))
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao atualizar status'
            ], 500);
        }
    }

    /**
     * ✅ FORMATAR PEDIDO PARA MOBILE - Com informações de distância
     */
    private function formatOrderForMobile($order)
    {
        return [
            'id' => $order->id,
            'status' => $order->status,
            'total_amount' => $order->total_amount,
            'delivery_fee' => $order->delivery_fee ?? 0,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            'accepted_at' => $order->accepted_at?->format('Y-m-d H:i:s'),
            'picked_up_at' => $order->picked_up_at?->format('Y-m-d H:i:s'),
            'delivered_at' => $order->delivered_at?->format('Y-m-d H:i:s'),

            // Restaurante
            'restaurant' => $order->restaurant ? [
                'id' => $order->restaurant->id,
                'name' => $order->restaurant->name,
                'phone' => $order->restaurant->phone,
                'address' => $order->restaurant->address,
                'latitude' => $order->restaurant->latitude,
                'longitude' => $order->restaurant->longitude,
                'image' => $order->restaurant->image,
            ] : null,

            // Cliente
            'customer' => $order->customer ? [
                'id' => $order->customer->id,
                'name' => $order->customer->name,
                'phone' => $order->customer->phone,
            ] : null,

            // Endereço de entrega
            'delivery_address' => [
                'street' => $order->delivery_address,
                'city' => $order->delivery_city ?? 'Maputo',
                'latitude' => $order->delivery_latitude,
                'longitude' => $order->delivery_longitude,
            ],

            // Itens do pedido
            'items' => $order->items ? $order->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'menu_item_id' => $item->menu_item_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'notes' => $item->notes,
                ];
            }) : [],
        ];
    }
}
