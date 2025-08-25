<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Iniciar pagamento M-Pesa
     * POST /api/v1/orders/{order}/payment/mpesa
     */
    public function initiateMpesaPayment(Order $order, Request $request)
    {
        // Verificar se o pedido pertence ao usuário
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:9|max:15'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Número de telefone inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Para desenvolvimento: simular sucesso
            if (config('app.env') === 'local') {
                return $this->simulatePaymentSuccess($order, 'mpesa', $request->phone_number);
            }

            // TODO: Implementar integração real com M-Pesa
            // Aqui você integraria com a API do M-Pesa

            return response()->json([
                'status' => 'success',
                'message' => 'Pagamento M-Pesa iniciado. Aguarde confirmação no seu telefone.',
                'data' => [
                    'order_id' => $order->id,
                    'payment_method' => 'mpesa',
                    'phone_number' => $request->phone_number,
                    'amount' => $order->total_amount,
                    'instructions' => 'Confirme o pagamento no seu telefone M-Pesa'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('M-Pesa payment error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar pagamento M-Pesa'
            ], 500);
        }
    }

    /**
     * Iniciar pagamento e-Mola
     * POST /api/v1/orders/{order}/payment/emola
     */
    public function initiateMolaPayment(Order $order, Request $request)
    {
        // Verificar se o pedido pertence ao usuário
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:9|max:15'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Número de telefone inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Para desenvolvimento: simular sucesso
            if (config('app.env') === 'local') {
                return $this->simulatePaymentSuccess($order, 'emola', $request->phone_number);
            }

            // TODO: Implementar integração real com e-Mola
            // Aqui você integraria com a API do e-Mola

            return response()->json([
                'status' => 'success',
                'message' => 'Pagamento e-Mola iniciado. Aguarde confirmação no seu telefone.',
                'data' => [
                    'order_id' => $order->id,
                    'payment_method' => 'emola',
                    'phone_number' => $request->phone_number,
                    'amount' => $order->total_amount,
                    'instructions' => 'Confirme o pagamento no seu telefone e-Mola'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('e-Mola payment error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar pagamento e-Mola'
            ], 500);
        }
    }

    /**
     * Confirmar pagamento em dinheiro
     * POST /api/v1/orders/{order}/payment/cash
     */
    public function confirmCashPayment(Order $order, Request $request)
    {
        // Verificar se o pedido pertence ao usuário
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Atualizar pedido
            $order->update([
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'status' => 'confirmed'
            ]);

            // Criar registro de pagamento
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'cash',
                'amount' => $order->total_amount,
                'status' => 'pending',
                'transaction_id' => 'CASH_' . $order->order_number,
                'payment_date' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pedido confirmado! O pagamento será feito na entrega.',
                'data' => [
                    'order_id' => $order->id,
                    'payment_method' => 'cash',
                    'amount' => $order->total_amount,
                    'message' => 'Tenha o valor exato em mãos para facilitar a entrega'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cash payment confirmation error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao confirmar pagamento em dinheiro'
            ], 500);
        }
    }

    /**
     * Confirmar pagamento (genérico)
     * POST /api/v1/orders/{order}/payment/confirm
     */
    public function confirmPayment(Order $order, Request $request)
    {
        // Verificar se o pedido pertence ao usuário
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'transaction_id' => 'sometimes|string',
            'confirmation_code' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados de confirmação inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Buscar pagamento existente
            $payment = Payment::where('order_id', $order->id)->first();

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pagamento não encontrado'
                ], 404);
            }

            // Atualizar pagamento
            $payment->update([
                'status' => 'completed',
                'transaction_id' => $request->transaction_id ?? $payment->transaction_id,
                'confirmation_data' => $request->all(),
                'confirmed_at' => now()
            ]);

            // Atualizar pedido
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pagamento confirmado com sucesso!',
                'data' => [
                    'order' => $order->fresh(['restaurant', 'items.menuItem']),
                    'payment' => $payment->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment confirmation error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao confirmar pagamento'
            ], 500);
        }
    }

    /**
     * Verificar status do pagamento
     * GET /api/v1/orders/{order}/payment/status
     */
    public function checkPaymentStatus(Order $order, Request $request)
    {
        // Verificar se o pedido pertence ao usuário
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        $payment = Payment::where('order_id', $order->id)->latest()->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'order_id' => $order->id,
                'order_status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'total_amount' => $order->total_amount,
                'payment_details' => $payment ? [
                    'id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'confirmed_at' => $payment->confirmed_at,
                ] : null
            ]
        ]);
    }

    /**
     * Listar métodos de pagamento disponíveis
     * GET /api/v1/payment/methods
     */
    public function getPaymentMethods()
    {
        $methods = [
            [
                'id' => 'cash',
                'name' => 'Dinheiro',
                'description' => 'Pagamento na entrega',
                'icon' => 'cash-outline',
                'enabled' => true,
                'requires_phone' => false
            ],
            [
                'id' => 'mpesa',
                'name' => 'M-Pesa',
                'description' => 'Pagamento móvel M-Pesa',
                'icon' => 'phone-portrait-outline',
                'enabled' => true,
                'requires_phone' => true
            ],
            [
                'id' => 'emola',
                'name' => 'e-Mola',
                'description' => 'Carteira digital e-Mola',
                'icon' => 'card-outline',
                'enabled' => true,
                'requires_phone' => true
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => $methods
        ]);
    }

    /**
     * Histórico de pagamentos do usuário
     * GET /api/v1/payment/history
     */
    public function getPaymentHistory(Request $request)
    {
        $payments = Payment::with(['order.restaurant'])
            ->whereHas('order', function($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $payments
        ]);
    }

    /**
     * Webhook para receber notificações de pagamento
     * POST /webhooks/payment
     */
    public function paymentWebhook(Request $request)
    {
        // Validar webhook (implementar validação de assinatura se necessário)

        try {
            $data = $request->all();

            Log::info('Payment webhook received', $data);

            // Buscar pedido
            $orderId = $data['order_id'] ?? $data['reference'] ?? null;

            if (!$orderId) {
                Log::warning('Payment webhook: missing order_id');
                return response()->json(['status' => 'error', 'message' => 'Missing order_id'], 400);
            }

            $order = Order::find($orderId);

            if (!$order) {
                Log::warning('Payment webhook: order not found', ['order_id' => $orderId]);
                return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
            }

            DB::beginTransaction();

            // Buscar ou criar pagamento
            $payment = Payment::where('order_id', $order->id)->first();

            if (!$payment) {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => $order->payment_method,
                    'amount' => $order->total_amount,
                    'status' => 'pending'
                ]);
            }

            // Atualizar com dados do webhook
            $status = $data['status'] ?? 'pending';
            $transactionId = $data['transaction_id'] ?? $data['external_id'] ?? null;

            $payment->update([
                'status' => $this->mapWebhookStatus($status),
                'transaction_id' => $transactionId,
                'external_data' => $data,
                'confirmed_at' => $status === 'success' ? now() : null
            ]);

            // Atualizar pedido se pagamento foi confirmado
            if ($status === 'success' || $status === 'completed') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => $order->status === 'pending' ? 'confirmed' : $order->status
                ]);
            }

            DB::commit();

            Log::info('Payment webhook processed successfully', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'status' => $status
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook processed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payment webhook error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Simular sucesso de pagamento (para desenvolvimento)
     */
    private function simulatePaymentSuccess(Order $order, string $method, string $phone = null)
    {
        try {
            DB::beginTransaction();

            // Criar registro de pagamento
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $method,
                'amount' => $order->total_amount,
                'status' => 'completed',
                'transaction_id' => strtoupper($method) . '_' . time() . '_' . rand(1000, 9999),
                'payment_date' => now(),
                'confirmed_at' => now()
            ]);

            // Atualizar pedido
            $order->update([
                'payment_method' => $method,
                'payment_status' => 'paid',
                'status' => 'confirmed'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Pagamento {$method} simulado com sucesso! (Ambiente de desenvolvimento)",
                'data' => [
                    'order_id' => $order->id,
                    'payment_method' => $method,
                    'phone_number' => $phone,
                    'amount' => $order->total_amount,
                    'transaction_id' => strtoupper($method) . '_SIMULATED',
                    'note' => 'Este é um pagamento simulado para desenvolvimento'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mapear status do webhook para status interno
     */
    private function mapWebhookStatus(string $webhookStatus): string
    {
        $statusMap = [
            'success' => 'completed',
            'completed' => 'completed',
            'confirmed' => 'completed',
            'failed' => 'failed',
            'error' => 'failed',
            'cancelled' => 'cancelled',
            'pending' => 'pending',
            'processing' => 'processing'
        ];

        return $statusMap[strtolower($webhookStatus)] ?? 'pending';
    }
}
