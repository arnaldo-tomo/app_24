@extends('layouts.admin')

@section('content')
<div class="min-h-full">
    <!-- Page content -->
    <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold leading-6 text-gray-900">Pagamentos</h1>
                    <p class="mt-2 text-sm text-gray-700">Gerencie todas as transações e métodos de pagamento</p>
                </div>
                <div class="mt-4 space-x-2 sm:ml-16 sm:mt-0 sm:flex-none">
                    <a href="{{ route('admin.payments.methods') }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-900 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <i class="mr-2 fas fa-credit-card"></i>Métodos
                    </a>
                    <a href="{{ route('admin.payments.analytics') }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-white bg-orange-600 rounded-md shadow-sm hover:bg-orange-500">
                        <i class="mr-2 fas fa-chart-bar"></i>Relatórios
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="p-4 mt-4 rounded-md bg-green-50">
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
                <div class="p-4 mt-4 rounded-md bg-red-50">
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

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 mt-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-green-500 fas fa-dollar-sign"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Processado</dt>
                                    <dd class="text-lg font-medium text-gray-900">MT {{ number_format($stats['total_amount'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-blue-500 fas fa-receipt"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Transações</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_transactions']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-yellow-500 fas fa-clock"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pendentes</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['pending_count']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-red-500 fas fa-percentage"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Taxas</dt>
                                    <dd class="text-lg font-medium text-gray-900">MT {{ number_format($stats['total_fee'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mt-8 bg-white rounded-lg shadow">
                <div class="px-4 py-5 sm:p-6">
                    <form method="GET" action="{{ route('admin.payments.index') }}">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Buscar</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="ID, pedido ou cliente">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Todos os status</option>
                                    @foreach(\App\Models\Payment::STATUSES as $value => $label)
                                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Método</label>
                                <select name="payment_method" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Todos os métodos</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->id }}" {{ request('payment_method') == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Data Início</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Data Fim</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full px-4 py-2 text-white bg-orange-600 rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <i class="mr-2 fas fa-search"></i>Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="flow-root mt-8">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">ID / Pedido</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Cliente</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Método</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Valor</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Data</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($payments as $payment)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $payment->transaction_id }}</div>
                                                <div class="text-sm text-gray-500">
                                                    @if($payment->order)
                                                        <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-orange-600 hover:text-orange-900">
                                                            {{ $payment->order->order_number }}
                                                        </a>
                                                    @else
                                                        Sem pedido
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($payment->user)
                                                <div class="text-sm text-gray-900">{{ $payment->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $payment->user->email }}</div>
                                            @else
                                                <span class="text-sm text-gray-500">Cliente não encontrado</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($payment->paymentMethod)
                                                <div class="flex items-center">
                                                    @if($payment->paymentMethod->icon)
                                                        <i class="mr-2 text-gray-400 {{ $payment->paymentMethod->icon }}"></i>
                                                    @endif
                                                    <span class="text-sm text-gray-900">{{ $payment->paymentMethod->name }}</span>
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $payment->paymentMethod->getTypeLabel() }}</div>
                                            @else
                                                <span class="text-sm text-gray-500">Método removido</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">MT {{ number_format($payment->amount, 2) }}</div>
                                            @if($payment->fee_amount > 0)
                                                <div class="text-xs text-gray-500">Taxa: MT {{ number_format($payment->fee_amount, 2) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
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
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                                {{ $payment->getStatusLabel() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $payment->created_at->format('d/m/Y') }}</div>
                                            <div class="text-sm text-gray-500">{{ $payment->created_at->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.payments.show', $payment) }}"
                                                   class="text-blue-600 hover:text-blue-900" title="Ver detalhes">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                @if($payment->canBeRefunded())
                                                    <button onclick="openRefundModal({{ $payment->id }}, {{ $payment->getRemainingRefundableAmount() }})"
                                                            class="text-purple-600 hover:text-purple-900" title="Estornar">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                @endif

                                                @if($payment->external_id)
                                                    <a href="#" class="text-green-600 hover:text-green-900" title="Ver no gateway">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <i class="mb-4 text-6xl text-gray-300 fas fa-credit-card"></i>
                                                <h3 class="mb-2 text-lg font-medium text-gray-900">Nenhum pagamento encontrado</h3>
                                                <p class="text-gray-500">Os pagamentos aparecerão aqui conforme os pedidos forem processados.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            @if($payments->hasPages())
            <div class="mt-6">
                {{ $payments->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </main>

    <!-- Refund Modal -->
    <div id="refundModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form id="refundForm" method="POST" action="">
                    @csrf
                    <div>
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-purple-100 rounded-full">
                            <i class="text-purple-600 fas fa-undo"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                Processar Estorno
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Digite o valor e motivo para processar o estorno desta transação.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label for="refund_amount" class="block text-sm font-medium text-gray-700">Valor do Estorno</label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">MT</span>
                                </div>
                                <input type="number" name="amount" id="refund_amount" step="0.01" min="0.01"
                                       class="block w-full pl-12 border-gray-300 rounded-md focus:border-purple-500 focus:ring-purple-500 sm:text-sm"
                                       placeholder="0.00" required>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Valor máximo disponível: <span id="max_refund_amount">MT 0.00</span></p>
                        </div>

                        <div>
                            <label for="refund_reason" class="block text-sm font-medium text-gray-700">Motivo do Estorno</label>
                            <textarea name="reason" id="refund_reason" rows="3" required
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm"
                                      placeholder="Digite o motivo do estorno..."></textarea>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="submit"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-purple-600 border border-transparent rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:col-start-2 sm:text-sm">
                            Processar Estorno
                        </button>
                        <button type="button" onclick="closeRefundModal()"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openRefundModal(paymentId, maxAmount) {
    const modal = document.getElementById('refundModal');
    const form = document.getElementById('refundForm');
    const amountInput = document.getElementById('refund_amount');
    const maxAmountSpan = document.getElementById('max_refund_amount');

    form.action = `/admin/payments/${paymentId}/refund`;
    amountInput.max = maxAmount;
    maxAmountSpan.textContent = `MT ${maxAmount.toFixed(2)}`;

    modal.classList.remove('hidden');
}

function closeRefundModal() {
    const modal = document.getElementById('refundModal');
    const form = document.getElementById('refundForm');

    modal.classList.add('hidden');
    form.reset();
}

// Close modal when clicking outside
document.getElementById('refundModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRefundModal();
    }
});
</script>
@endsection