<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushToken;
use App\Models\Notification;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Registrar token de push
     */
    public function registerPushToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'device_id' => 'nullable|string',
            'device_type' => 'nullable|string|in:mobile,web'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            // Verificar se token já existe
            $existingToken = PushToken::where('token', $request->token)->first();

            if ($existingToken) {
                // Atualizar token existente
                $existingToken->update([
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                    'device_type' => $request->device_type ?? 'mobile',
                    'is_active' => true,
                    'last_used_at' => now()
                ]);

                $token = $existingToken;
            } else {
                // Criar novo token
                $token = PushToken::create([
                    'user_id' => $user->id,
                    'token' => $request->token,
                    'device_id' => $request->device_id,
                    'device_type' => $request->device_type ?? 'mobile',
                    'is_active' => true,
                    'last_used_at' => now()
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Token registrado com sucesso',
                'data' => [
                    'token_id' => $token->id
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao registrar push token: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Remover token de push
     */
    public function unregisterPushToken(Request $request)
    {
        try {
            $user = $request->user();

            // Desativar todos os tokens do usuário ou um específico
            if ($request->has('token')) {
                PushToken::where('user_id', $user->id)
                         ->where('token', $request->token)
                         ->update(['is_active' => false]);
            } else {
                PushToken::where('user_id', $user->id)
                         ->update(['is_active' => false]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Token removido com sucesso'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao remover push token: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Listar notificações do usuário
     */
    public function getNotifications(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 20);

            $notifications = Notification::where('user_id', $user->id)
                                       ->orderBy('created_at', 'desc')
                                       ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => $notifications
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar notificações: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Marcar notificação como lida
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso negado'
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notificação marcada como lida'
        ]);
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();

            Notification::where('user_id', $user->id)
                       ->where('is_read', false)
                       ->update([
                           'is_read' => true,
                           'read_at' => now()
                       ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Todas as notificações foram marcadas como lidas'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao marcar notificações como lidas: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Contar notificações não lidas
     */
    public function getUnreadCount(Request $request)
    {
        try {
            $user = $request->user();

            $count = Notification::where('user_id', $user->id)
                                ->where('is_read', false)
                                ->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'unread_count' => $count
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao contar notificações: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Enviar notificação de teste
     */
    public function sendTestNotification(Request $request)
    {
        try {
            $user = $request->user();

            $success = $this->pushService->sendToUser(
                $user,
                'Notificação de Teste 🧪',
                'Esta é uma notificação de teste para verificar se tudo está funcionando!',
                ['type' => 'test'],
                'test'
            );

            return response()->json([
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Notificação de teste enviada' : 'Falha ao enviar notificação'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao enviar notificação de teste: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }
}

