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
     */
    public function initiateMpesaPayment(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        if ($order->payment_status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pagamento já processado'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|regex:/^(\+258|258|0)?[0-9]{8,9}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Número de telefone inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Buscar método de pagamento M-Pesa
            $paymentMethod = PaymentMethod::where('slug', 'mpesa')->where('is_active', true)->first();

            if (!$paymentMethod) {
                throw new \Exception('Método de pagamento M-Pesa não disponível');
            }

            $phoneNumber = $this->normalizePhoneNumber($request->phone_number);
            $transactionId = 'MPESA_' . time() . '_' . $order->id;

            // Calcular taxa de pagamento
            $feeAmount = $paymentMethod->calculateFee($order->total_amount);
            $netAmount = $order->total_amount - $feeAmount;

            // Criar registro de pagamento
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $paymentMethod->id,
                'user_id' => $request->user()->id,
                'transaction_id' => $transactionId,
                'amount' => $order->total_amount,
                'fee_amount' => $feeAmount,
                'net_amount' => $netAmount,
                'status' => 'pending',
                'description' => "Pagamento M-Pesa para pedido #{$order->order_number}",
                'metadata' => [
                    'phone_number' => $phoneNumber,
                    'provider' => 'mpesa',
                    'initiated_at' => now()->toISOString()
                ]
            ]);

            // Atualizar pedido
            $order->update([
                'payment_method' => 'mpesa',
                'payment_reference' => $transactionId
            ]);

            // Simular integração com M-Pesa API
            $mpesaResponse = $this->simulateMpesaRequest($order, $phoneNumber);

            if ($mpesaResponse['success']) {
                $payment->update([
                    'external_id' => $mpesaResponse['transaction_id'],
                    'status' => 'processing',
                    'metadata' => array_merge($payment->metadata, [
                        'external_transaction_id' => $mpesaResponse['transaction_id'],
                        'provider_message' => $mpesaResponse['message']
                    ])
                ]);

                DB::commit();

                Log::info('M-Pesa payment initiated', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'transaction_id' => $transactionId,
                    'phone_number' => $phoneNumber
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pagamento M-Pesa iniciado. Verifique seu telefone para confirmar.',
                    'data' => [
                        'payment' => [
                            'transaction_id' => $payment->transaction_id,
                            'amount' => $payment->amount,
                            'fee_amount' => $payment->fee_amount,
                            'phone_number' => $phoneNumber,
                            'estimated_confirmation_time' => '2-3 minutos'
                        ]
                    ]
                ]);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => $mpesaResponse['error']
                ]);

                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Falha ao iniciar pagamento M-Pesa: ' . $mpesaResponse['error']
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('M-Pesa payment error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Iniciar pagamento eMola
     */
    public function initiateMolaPayment(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        if ($order->payment_status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pagamento já processado'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|regex:/^(\+258|258|0)?[0-9]{8,9}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Número de telefone inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Buscar método de pagamento eMola
            $paymentMethod = PaymentMethod::where('slug', 'emola')->where('is_active', true)->first();

            if (!$paymentMethod) {
                throw new \Exception('Método de pagamento eMola não disponível');
            }

            $phoneNumber = $this->normalizePhoneNumber($request->phone_number);
            $transactionId = 'EMOLA_' . time() . '_' . $order->id;

            // Calcular taxa
            $feeAmount = $paymentMethod->calculateFee($order->total_amount);
            $netAmount = $order->total_amount - $feeAmount;

            // Criar pagamento
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $paymentMethod->id,
                'user_id' => $request->user()->id,
                'transaction_id' => $transactionId,
                'amount' => $order->total_amount,
                'fee_amount' => $feeAmount,
                'net_amount' => $netAmount,
                'status' => 'pending',
                'description' => "Pagamento eMola para pedido #{$order->order_number}",
                'metadata' => [
                    'phone_number' => $phoneNumber,
                    'provider' => 'emola',
                    'initiated_at' => now()->toISOString()
                ]
            ]);

            $order->update([
                'payment_method' => 'emola',
                'payment_reference' => $transactionId
            ]);

            // Simular integração eMola
            $emolaResponse = $this->simulateEmolaRequest($order, $phoneNumber);

            if ($emolaResponse['success']) {
                $payment->update([
                    'external_id' => $emolaResponse['transaction_id'],
                    'status' => 'processing',
                    'metadata' => array_merge($payment->metadata, [
                        'external_transaction_id' => $emolaResponse['transaction_id'],
                        'provider_message' => $emolaResponse['message']
                    ])
                ]);

                DB::commit();

                Log::info('eMola payment initiated', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'transaction_id' => $transactionId,
                    'phone_number' => $phoneNumber
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pagamento eMola iniciado. Verifique seu telefone para confirmar.',
                    'data' => [
                        'payment' => [
                            'transaction_id' => $payment->transaction_id,
                            'amount' => $payment->amount,
                            'fee_amount' => $payment->fee_amount,
                            'phone_number' => $phoneNumber,
                            'estimated_confirmation_time' => '1-2 minutos'
                        ]
                    ]
                ]);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => $emolaResponse['error']
                ]);

                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Falha ao iniciar pagamento eMola: ' . $emolaResponse['error']
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('eMola payment error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Confirmar pagamento em dinheiro
     */
    public function confirmCashPayment(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        if ($order->payment_status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pagamento já processado'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Buscar método de pagamento dinheiro
            $paymentMethod = PaymentMethod::where('slug', 'cash')->where('is_active', true)->first();

            if (!$paymentMethod) {
                throw new \Exception('Pagamento em dinheiro não disponível');
            }

            $transactionId = 'CASH_' . time() . '_' . $order->id;

            // Criar pagamento (sem taxa para dinheiro)
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $paymentMethod->id,
                'user_id' => $request->user()->id,
                'transaction_id' => $transactionId,
                'amount' => $order->total_amount,
                'fee_amount' => 0,
                'net_amount' => $order->total_amount,
                'status' => 'pending', // Será confirmado na entrega
                'description' => "Pagamento em dinheiro para pedido #{$order->order_number}",
                'metadata' => [
                    'payment_type' => 'cash_on_delivery',
                    'confirmed_at_delivery' => false
                ]
            ]);

            // Atualizar pedido - status especial para dinheiro
            $order->update([
                'payment_method' => 'cash',
                'payment_reference' => $transactionId,
                'payment_status' => 'pending', // Continua pendente até entrega
                'status' => 'confirmed' // Mas pedido é confirmado
            ]);

            DB::commit();

            Log::info('Cash payment confirmed', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pedido confirmado! Pagamento será feito na entrega.',
                'data' => [
                    'order' => $order->fresh(['restaurant', 'items.menuItem']),
                    'payment' => [
                        'transaction_id' => $payment->transaction_id,
                        'amount' => $payment->amount,
                        'payment_method' => 'Dinheiro na entrega'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cash payment error', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Confirmar pagamento (webhook ou manual)
     */
    public function confirmPayment(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'external_transaction_id' => 'required|string',
            'amount' => 'numeric|min:0',
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

            $payment = Payment::where('order_id', $order->id)
                              ->where('transaction_id', $request->transaction_id)
                              ->first();

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pagamento não encontrado'
                ], 404);
            }

            // Verificar se o valor confere (se fornecido)
            if ($request->has('amount') && $request->amount != $payment->amount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Valor do pagamento não confere'
                ], 400);
            }

            // Confirmar pagamento
            $payment->update([
                'status' => 'completed',
                'external_id' => $request->external_transaction_id,
                'payment_date' => now(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'confirmed_at' => now()->toISOString(),
                    'confirmation_method' => 'webhook'
                ])
            ]);

            // Atualizar pedido
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed'
            ]);

            DB::commit();

            Log::info('Payment confirmed', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'transaction_id' => $request->transaction_id,
                'external_transaction_id' => $request->external_transaction_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pagamento confirmado com sucesso!',
                'data' => [
                    'order' => $order->fresh(['restaurant', 'items.menuItem'])
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
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Verificar status do pagamento
     */
    public function checkPaymentStatus(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        $payment = Payment::with('paymentMethod')
                          ->where('order_id', $order->id)
                          ->latest()
                          ->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'order_status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'payment_reference' => $order->payment_reference,
                'total_amount' => $order->total_amount,
                'payment_details' => $payment ? [
                    'transaction_id' => $payment->transaction_id,
                    'status' => $payment->status,
                    'status_label' => $payment->getStatusLabel(),
                    'amount' => $payment->amount,
                    'fee_amount' => $payment->fee_amount,
                    'net_amount' => $payment->net_amount,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->paymentMethod->name ?? null,
                ] : null
            ]
        ]);
    }

    /**
     * Obter métodos de pagamento disponíveis
     */
    public function getPaymentMethods()
    {
        $paymentMethods = PaymentMethod::active()
                                      ->orderBy('sort_order')
                                      ->get()
                                      ->map(function ($method) {
                                          return [
                                              'id' => $method->id,
                                              'name' => $method->name,
                                              'slug' => $method->slug,
                                              'description' => $method->description,
                                              'icon' => $method->icon,
                                              'type' => $method->type,
                                              'type_label' => $method->getTypeLabel(),
                                              'fee_percentage' => $method->fee_percentage,
                                              'fee_fixed' => $method->fee_fixed,
                                          ];
                                      });

        return response()->json([
            'status' => 'success',
            'data' => $paymentMethods
        ]);
    }

    /**
     * Webhook para confirmações automáticas
     */
    public function paymentWebhook(Request $request)
    {
        // Verificar assinatura do webhook conforme provedor

        $transactionId = $request->transaction_id;
        $externalId = $request->external_transaction_id;
        $status = $request->status; // 'success', 'failed', etc.
        $amount = $request->amount;

        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            Log::warning('Webhook: Payment not found', ['transaction_id' => $transactionId]);
            return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
        }

        try {
            DB::beginTransaction();

            if ($status === 'success') {
                $payment->update([
                    'status' => 'completed',
                    'external_id' => $externalId,
                    'payment_date' => now(),
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'webhook_received_at' => now()->toISOString(),
                        'provider_status' => $status,
                        'provider_amount' => $amount
                    ])
                ]);

                $payment->order->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed'
                ]);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'external_id' => $externalId,
                    'failure_reason' => $request->failure_reason ?? 'Payment failed',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'webhook_received_at' => now()->toISOString(),
                        'provider_status' => $status,
                        'failure_details' => $request->all()
                    ])
                ]);
            }

            DB::commit();

            Log::info('Webhook processed successfully', [
                'transaction_id' => $transactionId,
                'external_id' => $externalId,
                'status' => $status,
                'payment_id' => $payment->id
            ]);

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Webhook processing error', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Métodos privados
     */
    private function simulateMpesaRequest($order, $phoneNumber)
    {
        sleep(1); // Simular delay de rede

        $success = rand(1, 10) <= 8; // 80% de sucesso

        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'MPESA_EXT_' . time() . rand(1000, 9999),
                'message' => 'Transação M-Pesa iniciada com sucesso'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Número de telefone inválido ou saldo insuficiente'
            ];
        }
    }

    private function simulateEmolaRequest($order, $phoneNumber)
    {
        sleep(1);

        $success = rand(1, 10) <= 9; // 90% de sucesso

        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'EMOLA_EXT_' . time() . rand(1000, 9999),
                'message' => 'Transação eMola iniciada com sucesso'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Conta eMola não encontrada ou saldo insuficiente'
            ];
        }
    }

    private function normalizePhoneNumber($phone)
    {
        // Remover espaços e caracteres especiais
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Normalizar para formato moçambicano
        if (str_starts_with($phone, '+258')) {
            $phone = substr($phone, 4);
        } elseif (str_starts_with($phone, '258')) {
            $phone = substr($phone, 3);
        } elseif (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }

        return '+258' . $phone;
    }
}