@extends('layouts.admin')

@section('content')
<div x-data="{ refundModal: false }" class="min-h-full">
    <!-- Page content -->
    <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <a href="{{ route('admin.payments.index') }}" class="text-gray-400 hover:text-gray-500">
                                <i class="flex-shrink-0 w-5 h-5 fas fa-credit-card"></i>
                                <span class="sr-only">Pagamentos</span>
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="flex-shrink-0 w-5 h-5 text-gray-300 fas fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium text-gray-500">{{ $payment->transaction_id }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                            Pagamento {{ $payment->transaction_id }}
                        </h1>
                        <div class="flex flex-col mt-1 sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                            <div class="flex items-center mt-2 text-sm text-gray-500">
                                <i class="flex-shrink-0 mr-1.5 text-gray-400 fas fa-calendar"></i>
                                {{ $payment->created_at->format('d/m/Y H:i') }}
                            </div>
                            @if($payment->order)
                            <div class="flex items-center mt-2 text-sm text-gray-500">
                                <i class="flex-shrink-0 mr-1.5 text-gray-400 fas fa-shopping-cart"></i>
                                <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-orange-600 hover:text-orange-900">
                                    {{ $payment->order->order_number }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex mt-4 md:ml-4 md:mt-0">
                        @if($payment->canBeRefunded())
                        <button @click="refundModal = true"
                                class="inline-flex items-center px-3 py-2 text-sm font-semibold text-white bg-purple-600 rounded-md shadow-sm hover:bg-purple-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600">
                            <i class="mr-2 fas fa-undo"></i>
                            Estornar
                        </button>
                        @endif
                        @if($payment->external_id)
                        <a href="#" target="_blank"
                           class="inline-flex items-center px-3 py-2 ml-3 text-sm font-semibold text-gray-900 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <i class="mr-2 fas fa-external-link-alt"></i>
                            Ver no Gateway
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="flex items-center mt-4 space-x-3">
                    @php
                        $statusColors = [
                            'pending' => 'yellow',
                            'processing' => 'blue',
                            'completed' => 'green',
                            'failed' => 'red',
                            'cancelled' => 'gray',
                            'refunded' => 'purple',
                            'partially_refunded' => 'purple'
                        ];
                        $color = $statusColors[$payment->status] ?? 'gray';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                        <span class="w-2 h-2 bg-{{ $color }}-400 rounded-full mr-2"></span>
                        {{ $payment->getStatusLabel() }}
                    </span>

                    @if($payment->isRefunded())
                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-purple-800 bg-purple-100 rounded-full">
                            <i class="mr-1 fas fa-undo"></i>
                            Estornado: MT {{ number_format($payment->getRefundedAmount(), 2) }}
                        </span>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="p-4 mb-6 rounded-md bg-green-50">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="text-green-400 fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 mb-6 rounded-md bg-red-50">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="text-red-400 fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main content -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Payment Details -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Detalhes do Pagamento</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">ID da Transação</dt>
                                    <dd class="mt-1 font-mono text-sm text-gray-900">{{ $payment->transaction_id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">ID Externo</dt>
                                    <dd class="mt-1 font-mono text-sm text-gray-900">{{ $payment->external_id ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Valor</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">MT {{ number_format($payment->amount, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Taxa</dt>
                                    <dd class="mt-1 text-sm text-gray-900">MT {{ number_format($payment->fee_amount, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Valor Líquido</dt>
                                    <dd class="mt-1 text-sm text-gray-900">MT {{ number_format($payment->net_amount, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Data do Pagamento</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y H:i') : 'N/A' }}
                                    </dd>
                                </div>
                                @if($payment->description)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Descrição</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $payment->description }}</dd>
                                </div>
                                @endif
                                @if($payment->failure_reason)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Motivo da Falha</dt>
                                    <dd class="mt-1 text-sm text-red-600">{{ $payment->failure_reason }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Payment Method Info -->
                    @if($payment->paymentMethod)
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Método de Pagamento</h2>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-12 h-12 bg-orange-100 rounded-lg">
                                    @if($payment->paymentMethod->icon)
                                        <i class="text-orange-600 {{ $payment->paymentMethod->icon }}"></i>
                                    @else
                                        <i class="text-orange-600 fas fa-credit-card"></i>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">{{ $payment->paymentMethod->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $payment->paymentMethod->getTypeLabel() }}</p>
                                    @if($payment->paymentMethod->description)
                                        <p class="mt-1 text-sm text-gray-600">{{ $payment->paymentMethod->description }}</p>
                                    @endif
                                </div>
                            </div>

                            @if($payment->paymentMethod->fee_percentage > 0 || $payment->paymentMethod->fee_fixed > 0)
                            <div class="p-3 mt-4 rounded-lg bg-gray-50">
                                <h4 class="text-sm font-medium text-gray-700">Configuração de Taxas</h4>
                                <div class="mt-2 text-sm text-gray-600">
                                    @if($payment->paymentMethod->fee_percentage > 0)
                                        <span>{{ number_format($payment->paymentMethod->fee_percentage, 2) }}%</span>
                                    @endif
                                    @if($payment->paymentMethod->fee_percentage > 0 && $payment->paymentMethod->fee_fixed > 0)
                                        <span> + </span>
                                    @endif
                                    @if($payment->paymentMethod->fee_fixed > 0)
                                        <span>MT {{ number_format($payment->paymentMethod->fee_fixed, 2) }}</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Customer Info -->
                    @if($payment->user)
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Cliente</h2>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($payment->user->avatar)
                                        <img class="object-cover w-12 h-12 rounded-full" src="{{ asset('storage/' . $payment->user->avatar) }}" alt="{{ $payment->user->name }}">
                                    @else
                                        <div class="flex items-center justify-center w-12 h-12 bg-gray-200 rounded-full">
                                            <i class="text-gray-400 fas fa-user"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">{{ $payment->user->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $payment->user->email }}</p>
                                    @if($payment->user->phone)
                                        <p class="text-sm text-gray-500">{{ $payment->user->phone }}</p>
                                    @endif
                                </div>
                                <div class="ml-auto">
                                    <a href="{{ route('admin.users.show', $payment->user) }}"
                                       class="text-orange-600 hover:text-orange-900">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Order Info -->
                    @if($payment->order)
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Pedido Relacionado</h2>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ $payment->order->order_number }}</h3>
                                    <p class="text-sm text-gray-500">{{ $payment->order->created_at->format('d/m/Y H:i') }}</p>
                                    <p class="text-sm text-gray-600">
                                        Total: MT {{ number_format($payment->order->total_amount, 2) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($payment->order->status) }}
                                    </span>
                                    <div class="mt-2">
                                        <a href="{{ route('admin.orders.show', $payment->order) }}"
                                           class="text-orange-600 hover:text-orange-900">
                                            <i class="mr-1 fas fa-external-link-alt"></i>
                                            Ver Pedido
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Refunds History -->
                    @if($payment->refunds->count() > 0)
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Histórico de Estornos</h2>
                        </div>
                        <div class="overflow-hidden">
                            <ul class="divide-y divide-gray-200">
                                @foreach($payment->refunds as $refund)
                                <li class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $refund->refund_id }}</div>
                                            <div class="text-sm text-gray-500">{{ $refund->reason }}</div>
                                            <div class="text-xs text-gray-400">
                                                Por: {{ $refund->processedBy->name ?? 'Sistema' }} em {{ $refund->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-medium text-gray-900">MT {{ number_format($refund->amount, 2) }}</div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $refund->isCompleted() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $refund->getStatusLabel() }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Resumo</h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Valor Original</dt>
                                    <dd class="text-lg font-bold text-gray-900">MT {{ number_format($payment->amount, 2) }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Taxa Cobrada</dt>
                                    <dd class="text-sm text-red-600">- MT {{ number_format($payment->fee_amount, 2) }}</dd>
                                </div>
                                @if($payment->getRefundedAmount() > 0)
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Total Estornado</dt>
                                    <dd class="text-sm text-purple-600">- MT {{ number_format($payment->getRefundedAmount(), 2) }}</dd>
                                </div>
                                @endif
                                <div class="pt-4 border-t border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Valor Líquido</dt>
                                        <dd class="text-lg font-bold text-green-600">MT {{ number_format($payment->net_amount - $payment->getRefundedAmount(), 2) }}</dd>
                                    </div>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    @if($payment->canBeRefunded())
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Ações</h2>
                        </div>
                        <div class="p-6">
                            <button @click="refundModal = true"
                                    class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700">
                                <i class="mr-2 fas fa-undo"></i>
                                Processar Estorno
                            </button>
                            <p class="mt-2 text-xs text-center text-gray-500">
                                Disponível para estorno: MT {{ number_format($payment->getRemainingRefundableAmount(), 2) }}
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Metadata -->
                    @if($payment->metadata)
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Metadados</h2>
                        </div>
                        <div class="p-6">
                            <pre class="p-3 overflow-auto text-xs text-gray-600 rounded bg-gray-50">{{ json_encode($payment->metadata, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <!-- Refund Modal -->
    <div x-show="refundModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="refundModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="refundModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                <form action="{{ route('admin.payments.refund', $payment) }}" method="POST">
                    @csrf
                    <div>
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-purple-100 rounded-full">
                            <i class="text-purple-600 fas fa-undo"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">
                                Processar Estorno
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Processe um estorno para esta transação. O valor será devolvido ao cliente.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Valor do Estorno</label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">MT</span>
                                </div>
                                <input type="number" name="amount" id="amount" step="0.01"
                                       min="0.01" max="{{ $payment->getRemainingRefundableAmount() }}"
                                       class="block w-full pl-12 border-gray-300 rounded-md focus:border-purple-500 focus:ring-purple-500 sm:text-sm"
                                       placeholder="0.00" required>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Máximo disponível: MT {{ number_format($payment->getRemainingRefundableAmount(), 2) }}
                            </p>
                        </div>

                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700">Motivo do Estorno</label>
                            <textarea name="reason" id="reason" rows="3" required
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm"
                                      placeholder="Descreva o motivo do estorno..."></textarea>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="submit"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-purple-600 border border-transparent rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:col-start-2 sm:text-sm">
                            Processar Estorno
                        </button>
                        <button type="button" @click="refundModal = false"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection