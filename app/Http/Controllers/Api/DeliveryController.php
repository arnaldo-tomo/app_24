<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function availableOrders(Request $request)
    {
        $user = $request->user();

        if (!$user->isDeliveryPerson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        $orders = Order::with(['restaurant', 'customer'])
                      ->where('status', 'ready')
                      ->whereNull('delivery_person_id')
                      ->latest()
                      ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function acceptOrder(Request $request, Order $order)
    {
        $user = $request->user();

        if (!$user->isDeliveryPerson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        if ($order->status !== 'ready' || $order->delivery_person_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pedido não está disponível para entrega'
            ], 400);
        }

        $order->update([
            'delivery_person_id' => $user->id,
            'status' => 'picked_up'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pedido aceito para entrega',
            'data' => [
                'order' => $order->fresh(['restaurant', 'customer'])
            ]
        ]);
    }

    public function myDeliveries(Request $request)
    {
        $user = $request->user();

        if (!$user->isDeliveryPerson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        $orders = Order::with(['restaurant', 'customer'])
                      ->where('delivery_person_id', $user->id)
                      ->latest()
                      ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function updateDeliveryStatus(Request $request, Order $order)
    {
        $user = $request->user();

        if (!$user->isDeliveryPerson() || $order->delivery_person_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
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

        $updateData = ['status' => $request->status];

        if ($request->status === 'delivered') {
            $updateData['delivered_at'] = now();
        }

        $order->update($updateData);

        // Update delivery person location if provided
        if ($request->has('latitude') && $request->has('longitude')) {
            $user->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Status da entrega atualizado',
            'data' => [
                'order' => $order->fresh(['restaurant', 'customer'])
            ]
        ]);
    }
}