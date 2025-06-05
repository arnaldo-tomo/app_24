@extends('layouts.admin')

@section('content')
<div x-data="{
    orderDetailsModal: false,
    selectedOrder: null,
    selectedOrderData: null
}" class="min-h-full">

    <!-- Page content -->
    <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold leading-6 text-gray-900">Gestão de Pedidos</h1>
                    <p class="mt-2 text-sm text-gray-700">Monitore e gerencie todos os pedidos do sistema</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <button type="button" onclick="window.location.reload()" class="block px-3 py-2 text-sm font-semibold text-center text-white bg-orange-600 rounded-md shadow-sm hover:bg-orange-500">
                        <i class="mr-2 fas fa-refresh"></i>Atualizar
                    </button>
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

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 mt-8 sm:grid-cols-2 lg:grid-cols-5">
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-blue-500 fas fa-shopping-bag"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Hoje</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ \App\Models\Order::whereDate('created_at', today())->count() }}</dd>
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
                                    <dd class="text-lg font-medium text-gray-900">{{ \App\Models\Order::where('status', 'pending')->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-orange-500 fas fa-fire"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Preparando</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ \App\Models\Order::where('status', 'preparing')->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-blue-500 fas fa-motorcycle"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Em Entrega</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ \App\Models\Order::where('status', 'picked_up')->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-green-500 fas fa-check-circle"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Entregues</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ \App\Models\Order::where('status', 'delivered')->whereDate('created_at', today())->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mt-8 bg-white rounded-lg shadow">
                <div class="px-4 py-5 sm:p-6">
                    <form method="GET" action="{{ route('admin.orders.index') }}">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Buscar</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="Número do pedido, cliente...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Todos os status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                                    <option value="preparing" {{ request('status') == 'preparing' ? 'selected' : '' }}>Preparando</option>
                                    <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Pronto</option>
                                    <option value="picked_up" {{ request('status') == 'picked_up' ? 'selected' : '' }}>Em Entrega</option>
                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Entregue</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pagamento</label>
                                <select name="payment_status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Todos</option>
                                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Pago</option>
                                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Falhou</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Data</label>
                                <select name="date_filter" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Todas as datas</option>
                                    <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Hoje</option>
                                    <option value="yesterday" {{ request('date_filter') == 'yesterday' ? 'selected' : '' }}>Ontem</option>
                                    <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>Esta semana</option>
                                    <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>Este mês</option>
                                </select>
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

            <!-- Orders Table -->
            <div class="flow-root mt-8">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Pedido</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Cliente</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Restaurante</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Pagamento</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Total</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Data</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($orders as $order)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-10 h-10">
                                                    <div class="flex items-center justify-center w-10 h-10 bg-orange-100 rounded-full">
                                                        <i class="text-orange-600 fas fa-shopping-bag"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $order->order_number }}</div>
                                                    <div class="text-sm text-gray-500">{{ $order->items->count() }} itens</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $order->customer->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $order->customer->phone ?? $order->customer->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if($order->restaurant->image)
                                                    <img class="object-cover w-8 h-8 mr-3 rounded-lg" src="{{ asset('storage/' . $order->restaurant->image) }}" alt="{{ $order->restaurant->name }}">
                                                @else
                                                    <div class="flex items-center justify-center w-8 h-8 mr-3 bg-gray-200 rounded-lg">
                                                        <i class="text-xs text-gray-400 fas fa-store"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $order->restaurant->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $order->restaurant->owner->name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($order->status)
                                                @case('pending')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full mr-1.5"></span>
                                                        Pendente
                                                    </span>
                                                    @break
                                                @case('confirmed')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <span class="w-1.5 h-1.5 bg-blue-400 rounded-full mr-1.5"></span>
                                                        Confirmado
                                                    </span>
                                                    @break
                                                @case('preparing')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                        <span class="w-1.5 h-1.5 bg-orange-400 rounded-full mr-1.5"></span>
                                                        Preparando
                                                    </span>
                                                    @break
                                                @case('ready')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                        <span class="w-1.5 h-1.5 bg-purple-400 rounded-full mr-1.5"></span>
                                                        Pronto
                                                    </span>
                                                    @break
                                                @case('picked_up')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full mr-1.5"></span>
                                                        Em Entrega
                                                    </span>
                                                    @break
                                                @case('delivered')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                        Entregue
                                                    </span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                                        Cancelado
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                @switch($order->payment_status)
                                                    @case('paid')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="mr-1 fas fa-check"></i>
                                                            Pago
                                                        </span>
                                                        @break
                                                    @case('pending')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            <i class="mr-1 fas fa-clock"></i>
                                                            Pendente
                                                        </span>
                                                        @break
                                                    @case('failed')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <i class="mr-1 fas fa-times"></i>
                                                            Falhou
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            {{ ucfirst($order->payment_status) }}
                                                        </span>
                                                @endswitch
                                                <span class="mt-1 text-xs text-gray-500">{{ ucfirst($order->payment_method) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">MT {{ number_format($order->total_amount, 2) }}</div>
                                            <div class="text-sm text-gray-500">
                                                Subtotal: MT {{ number_format($order->subtotal, 2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $order->created_at->format('d/m/Y') }}</div>
                                            <div class="text-sm text-gray-500">{{ $order->created_at->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <button>



                                                </button>

                                                @if($order->deliveryPerson)
                                                    <span class="text-green-600" title="Entregador: {{ $order->deliveryPerson->name }}">
                                                        <i class="fas fa-motorcycle"></i>
                                                    </span>
                                                @endif

                                                @if($order->canBeCancelled())
                                                    <button class="text-red-600 hover:text-red-900" title="Cancelar pedido">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <i class="mb-4 text-6xl text-gray-300 fas fa-shopping-bag"></i>
                                                <h3 class="mb-2 text-lg font-medium text-gray-900">Nenhum pedido encontrado</h3>
                                                <p class="text-gray-500">Não há pedidos que correspondam aos filtros selecionados.</p>
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
            @if($orders->hasPages())
            <div class="mt-6">
                {{ $orders->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </main>

    <!-- Order Details Modal -->
    <div x-show="orderDetailsModal" class="relative z-50" role="dialog" style="display: none;">
        <div x-show="orderDetailsModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0">
                <div x-show="orderDetailsModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     class="relative px-4 pt-5 pb-4 overflow-hidden text-left transform bg-white rounded-lg shadow-xl sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">

                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Detalhes do Pedido</h3>
                        <button @click="orderDetailsModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div x-show="selectedOrderData" class="space-y-6">
                        <!-- Order Info -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Número do Pedido</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedOrderData?.order_number"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Data</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedOrderData?.created_at"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedOrderData?.customer"></p>
                                <p class="mt-1 text-xs text-gray-500" x-text="selectedOrderData?.customer_phone"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Restaurante</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedOrderData?.restaurant"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedOrderData?.status"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Entregador</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedOrderData?.delivery_person"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pagamento</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    <span x-text="selectedOrderData?.payment_method"></span> -
                                    <span x-text="selectedOrderData?.payment_status"></span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total</label>
                                <p class="mt-1 text-lg font-bold text-gray-900">MT <span x-text="selectedOrderData?.total"></span></p>
                            </div>
                        </div>

                        <!-- Delivery Address -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Endereço de Entrega</label>
                            <p class="mt-1 text-sm text-gray-900" x-text="selectedOrderData?.delivery_address"></p>
                        </div>

                        <!-- Order Items -->
                        <div>
                            <label class="block mb-3 text-sm font-medium text-gray-700">Itens do Pedido</label>
                            <div class="p-4 rounded-lg bg-gray-50">
                                <div class="space-y-3">
                                    <template x-for="item in selectedOrderData?.items || []" :key="item.name">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center">
                                                    <span class="text-sm font-medium text-gray-900" x-text="item.name"></span>
                                                    <span class="ml-2 text-sm text-gray-500">x<span x-text="item.quantity"></span></span>
                                                </div>
                                                <div x-show="item.special_instructions" class="mt-1">
                                                    <span class="text-xs font-medium text-orange-600">Observações:</span>
                                                    <span class="text-xs text-gray-600" x-text="item.special_instructions"></span>
                                                </div>
                                            </div>
                                            <div class="ml-4 text-right">
                                                <div class="text-sm font-medium text-gray-900">MT <span x-text="item.total_price"></span></div>
                                                <div class="text-xs text-gray-500">MT <span x-text="item.unit_price"></span> cada</div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button @click="orderDetailsModal = false" class="px-3 py-2 text-sm font-semibold text-gray-900 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection