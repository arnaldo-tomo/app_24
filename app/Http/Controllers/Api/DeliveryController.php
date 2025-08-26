<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    // Constantes para configuração
    const DEFAULT_RADIUS_KM = 5; // Raio padrão de 5km
    const EARTH_RADIUS_KM = 6371; // Raio da Terra em km

    /**
     * ENTREGAS DISPONÍVEIS COM LÓGICA DE PROXIMIDADE - Estilo Uber Eats
     *
     * Implementa distribuição inteligente baseada na localização do entregador:
     * 1. Filtra pedidos prontos (status 'ready')
     * 2. Sem entregador atribuído (delivery_person_id = null)
     * 3. Apenas pedidos pagos (payment_status = 'paid')
     * 4. Dentro do raio configurável do entregador
     * 5. Ordenados por proximidade (mais próximos primeiro)
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

        // Validar coordenadas do entregador
        if (!$user->latitude || !$user->longitude) {
            return response()->json([
                'status' => 'error',
                'message' => 'Localização não encontrada. Ative o GPS e tente novamente.',
                'code' => 'LOCATION_REQUIRED'
            ], 400);
        }

        try {
            \Log::info('Buscando pedidos disponíveis para entregador: ' . $user->id);
            \Log::info('Localização do entregador: ' . $user->latitude . ', ' . $user->longitude);

            // Parâmetros configuráveis
            $radiusKm = $request->input('radius', self::DEFAULT_RADIUS_KM);
            $maxOrders = $request->input('max_orders', 20);

            // Query otimizada com cálculo de distância
            $orders = Order::with([
                'restaurant:id,name,phone,address,latitude,longitude,image',
                'customer:id,name,phone,email,address',
                'items:id,order_id,menu_item_id,quantity,price,notes',
                'items.menuItem:id,name,description,price,image'
            ])
            ->select('*')
            // Adicionar cálculo de distância usando fórmula Haversine
            ->selectRaw("
                (
                    " . self::EARTH_RADIUS_KM . " * acos(
                        cos(radians(?)) *
                        cos(radians(restaurants.latitude)) *
                        cos(radians(restaurants.longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(restaurants.latitude))
                    )
                ) as distance_km",
                [$user->latitude, $user->longitude, $user->latitude]
            )
            ->join('restaurants', 'orders.restaurant_id', '=', 'restaurants.id')
            ->where('orders.status', 'ready')                    // Pedido pronto para coleta
            ->whereNull('orders.delivery_person_id')             // Sem entregador atribuído
            ->where('orders.payment_status', 'paid')             // Apenas pedidos pagos
            ->whereNotNull('restaurants.latitude')               // Restaurante com coordenadas
            ->whereNotNull('restaurants.longitude')
            // Filtro por raio usando SQL (mais eficiente que PHP)
            ->whereRaw("
                (
                    " . self::EARTH_RADIUS_KM . " * acos(
                        cos(radians(?)) *
                        cos(radians(restaurants.latitude)) *
                        cos(radians(restaurants.longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(restaurants.latitude))
                    )
                ) <= ?",
                [$user->latitude, $user->longitude, $user->latitude, $radiusKm]
            )
            ->orderBy('distance_km', 'asc')                      // Mais próximos primeiro
            ->orderBy('orders.created_at', 'asc')                // Em caso de empate, mais antigos primeiro
            ->limit($maxOrders)
            ->get();

            \Log::info('Encontrados ' . $orders->count() . ' pedidos disponíveis no raio de ' . $radiusKm . 'km');

            // Formatar dados para o app mobile
            $formattedOrders = $orders->map(function($order) use ($user) {
                $formatted = $this->formatOrderForMobile($order);

                // Adicionar informações de distância e tempo estimado
                $formatted['distance_km'] = round($order->distance_km, 1);
                $formatted['distance_text'] = $this->formatDistance($order->distance_km);
                $formatted['estimated_pickup_time'] = $this->estimatePickupTime($order->distance_km);

                // Adicionar coordenadas do restaurante para navegação
                $formatted['restaurant_coordinates'] = [
                    'latitude' => (float) $order->restaurant->latitude,
                    'longitude' => (float) $order->restaurant->longitude
                ];

                // Adicionar coordenadas de entrega
                $deliveryCoords = $this->parseDeliveryCoordinates($order->delivery_address);
                if ($deliveryCoords) {
                    $formatted['delivery_coordinates'] = $deliveryCoords;

                    // Calcular distância total da rota (restaurante -> cliente)
                    $totalDistance = $this->calculateDistance(
                        (float) $order->restaurant->latitude,
                        (float) $order->restaurant->longitude,
                        $deliveryCoords['latitude'],
                        $deliveryCoords['longitude']
                    );
                    $formatted['total_route_distance'] = $totalDistance;
                    $formatted['estimated_delivery_time'] = $this->estimateDeliveryTime($order->distance_km, $totalDistance);
                }

                return $formatted;
            });

            // Metadados úteis para o app
            $metadata = [
                'entregador' => [
                    'id' => $user->id,
                    'nome' => $user->name,
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude
                ],
                'filtros' => [
                    'raio_km' => $radiusKm,
                    'max_pedidos' => $maxOrders,
                    'total_encontrados' => $orders->count()
                ],
                'configuracoes' => [
                    'pode_alterar_raio' => true,
                    'raio_minimo' => 1,
                    'raio_maximo' => 15
                ]
            ];

            return response()->json([
                'status' => 'success',
                'message' => "Encontrados {$orders->count()} pedidos no raio de {$radiusKm}km",
                'data' => $formattedOrders,
                'metadata' => $metadata
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar pedidos disponíveis: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao carregar pedidos disponíveis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ACEITAR PEDIDO com validações de proximidade
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

        // Verificar se entregador está próximo o suficiente (opcional)
        if ($user->latitude && $user->longitude && $order->restaurant) {
            $distance = $this->calculateDistance(
                $user->latitude,
                $user->longitude,
                $order->restaurant->latitude,
                $order->restaurant->longitude
            );

            // Se estiver muito longe (>10km), avisar mas permitir
            if ($distance > 10) {
                \Log::warning("Entregador {$user->id} aceitando pedido distante: {$distance}km");
            }
        }

        try {
            \Log::info("Entregador {$user->id} aceitando pedido {$order->id}");

            $order->update([
                'delivery_person_id' => $user->id,
                'status' => 'picked_up'    // Entregador saiu para coleta
            ]);

            // Atualizar localização do entregador se fornecida
            if ($request->has('latitude') && $request->has('longitude')) {
                $user->update([
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude
                ]);
            }

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
            ->where('delivery_person_id', $user->id)
            ->orderBy('created_at', 'desc')
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
     * ATUALIZAR STATUS - Fluxo da entrega com localização
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

            // Sempre atualizar localização do entregador se fornecida
            if ($request->has('latitude') && $request->has('longitude')) {
                $user->update([
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude
                ]);
                \Log::info("Localização do entregador {$user->id} atualizada durante entrega");
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
     * ATUALIZAR LOCALIZAÇÃO DO ENTREGADOR
     */
    public function updateLocation(Request $request)
    {
        $user = $request->user();

        if (!$user->isDeliveryPerson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        $validator = \Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Coordenadas inválidas',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude
            ]);

            \Log::info("Localização do entregador {$user->id} atualizada: {$request->latitude}, {$request->longitude}");

            return response()->json([
                'status' => 'success',
                'message' => 'Localização atualizada com sucesso'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar localização: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao atualizar localização',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =============== MÉTODOS AUXILIARES ===============

    /**
     * Calcular distância entre duas coordenadas usando fórmula Haversine
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    /**
     * Formatar distância para exibição
     */
    private function formatDistance($distanceKm)
    {
        if ($distanceKm < 1) {
            return round($distanceKm * 1000) . 'm';
        }
        return round($distanceKm, 1) . 'km';
    }

    /**
     * Estimar tempo para chegar ao restaurante
     */
    private function estimatePickupTime($distanceKm)
    {
        // Velocidade média: 25 km/h na cidade
        $minutes = ($distanceKm / 25) * 60;
        return max(5, round($minutes)); // Mínimo 5 minutos
    }

    /**
     * Estimar tempo total de entrega
     */
    private function estimateDeliveryTime($pickupDistanceKm, $deliveryDistanceKm)
    {
        $pickupTime = $this->estimatePickupTime($pickupDistanceKm);
        $deliveryTime = max(10, ($deliveryDistanceKm / 25) * 60); // Tempo de entrega
        $preparationTime = 10; // Tempo de preparação/coleta

        return round($pickupTime + $preparationTime + $deliveryTime);
    }

    /**
     * Extrair coordenadas do endereço de entrega
     */
    private function parseDeliveryCoordinates($deliveryAddress)
    {
        if (!$deliveryAddress) return null;

        // Se é JSON string, decodificar
        if (is_string($deliveryAddress)) {
            $address = json_decode($deliveryAddress, true);
        } else {
            $address = $deliveryAddress;
        }

        // Verificar se tem coordenadas
        if (isset($address['latitude']) && isset($address['longitude'])) {
            return [
                'latitude' => (float) $address['latitude'],
                'longitude' => (float) $address['longitude']
            ];
        }

        return null;
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
            'total_amount' => $order->total_amount,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'delivery_address' => $order->delivery_address,
            'notes' => $order->notes,
            'created_at' => $order->created_at,
            'delivered_at' => $order->delivered_at,

            // Relacionamentos
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

            'items' => $order->items ? $order->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'notes' => $item->notes,
                    'menu_item' => $item->menuItem ? [
                        'id' => $item->menuItem->id,
                        'name' => $item->menuItem->name,
                        'description' => $item->menuItem->description,
                        'price' => $item->menuItem->price,
                        'image' => $item->menuItem->image,
                    ] : null,
                ];
            }) : [],
        ];
    }
}
