@extends('layouts.restaurant')

@section('content')
<div x-data="{
    orderStatusModal: false,
    selectedOrder: null,
    updateOrderStatus() {
        const status = document.getElementById('orderStatus').value;

        fetch(`/restaurant/orders/${this.selectedOrder}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao atualizar status do pedido');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao atualizar status do pedido');
        });
    }
}" class="min-h-full">

    <!-- Dashboard content -->
    <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Dashboard header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Dashboard do {{ $restaurant->name }}</h1>
                <p class="mt-2 text-sm text-gray-700">Gerencie seus pedidos e monitore o desempenho</p>
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

            <!-- Stats -->
            <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Orders Today -->
                <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-lg">
                                    <i class="text-blue-600 fas fa-shopping-bag"></i>
                                </div>
                            </div>
                            <div class="flex-1 w-0 ml-4">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pedidos Hoje</dt>
                                    <dd class="text-2xl font-bold text-gray-900">{{ $stats['orders_today'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Today -->
                <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-lg">
                                    <i class="text-green-600 fas fa-dollar-sign"></i>
                                </div>
                            </div>
                            <div class="flex-1 w-0 ml-4">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Receita Hoje</dt>
                                    <dd class="text-2xl font-bold text-gray-900">MT {{ number_format($stats['revenue_today'], 2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Orders -->
                <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 bg-orange-100 rounded-lg">
                                    <i class="text-orange-600 fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="flex-1 w-0 ml-4">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pedidos Pendentes</dt>
                                    <dd class="text-2xl font-bold text-gray-900">{{ $stats['pending_orders'] }}</dd>
                                </dl>
                            </div>
                        </div>
                        @if($stats['pending_orders'] > 0)
                        <div class="mt-4">
                            <a href="{{ route('restaurant.orders.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-500">
                                Ver pedidos pendentes →
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Active Menu Items -->
                <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 bg-purple-100 rounded-lg">
                                    <i class="text-purple-600 fas fa-utensils"></i>
                                </div>
                            </div>
                            <div class="flex-1 w-0 ml-4">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Itens Disponíveis</dt>
                                    <dd class="text-2xl font-bold text-gray-900">{{ $stats['active_menu_items'] }}</dd>
                                </dl>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('restaurant.menu.index') }}" class="text-sm font-medium text-purple-600 hover:text-purple-500">
                                Gerenciar menu →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders and Popular Items -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- Recent Orders -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Pedidos Recentes</h3>
                        <a href="{{ route('restaurant.orders.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-500">Ver todos</a>
                    </div>
                    <div class="flow-root">
                        <ul role="list" class="divide-y divide-gray-200">
                            @forelse($recent_orders as $order)
                            <li class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full {{ $order->isPending() ? 'bg-yellow-100' : ($order->isConfirmed() ? 'bg-blue-100' : ($order->isPreparing() ? 'bg-orange-100' : ($order->isReady() ? 'bg-green-100' : 'bg-gray-100'))) }} flex items-center justify-center">
                                            <span class="text-sm font-medium {{ $order->isPending() ? 'text-yellow-800' : ($order->isConfirmed() ? 'text-blue-800' : ($order->isPreparing() ? 'text-orange-800' : ($order->isReady() ? 'text-green-800' : 'text-gray-800'))) }}">
                                                #{{ substr($order->order_number, -3) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $order->customer->name }}</p>
                                        <p class="text-sm text-gray-500 truncate">{{ $order->items->count() }} itens</p>
                                    </div>
                                    <div class="flex-shrink-0 text-right">
                                        <p class="text-sm font-medium text-gray-900">MT {{ number_format($order->total_amount, 2) }}</p>
                                        <p class="text-sm">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $order->isPending() ? 'bg-yellow-100 text-yellow-800' :
                                                   ($order->isConfirmed() ? 'bg-blue-100 text-blue-800' :
                                                   ($order->isPreparing() ? 'bg-orange-100 text-orange-800' :
                                                   ($order->isReady() ? 'bg-green-100 text-green-800' :
                                                   ($order->isDelivered() ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800')))) }}">
                                                @switch($order->status)
                                                    @case('pending') Pendente @break
                                                    @case('confirmed') Confirmado @break
                                                    @case('preparing') Preparando @break
                                                    @case('ready') Pronto @break
                                                    @case('picked_up') Retirado @break
                                                    @case('delivered') Entregue @break
                                                    @case('cancelled') Cancelado @break
                                                    @default {{ $order->status }}
                                                @endswitch
                                            </span>
                                        </p>
                                    </div>
                                    @if($order->isPending() || $order->isConfirmed() || $order->isPreparing())
                                    <div class="flex-shrink-0">
                                        <button @click="selectedOrder = {{ $order->id }}; orderStatusModal = true" class="text-orange-600 hover:text-orange-500">
                                            <i class="w-4 h-4 fas fa-edit"></i>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </li>
                            @empty
                            <li class="px-6 py-8 text-center">
                                <i class="text-3xl text-gray-300 fas fa-shopping-bag"></i>
                                <p class="mt-2 text-sm text-gray-500">Nenhum pedido ainda</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- Popular Items -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Itens Mais Populares</h3>
                        <a href="{{ route('restaurant.menu.index') }}" class="text-sm font-medium text-orange-600 hover:text-orange-500">Ver menu</a>
                    </div>
                    <div class="flow-root">
                        <ul role="list" class="divide-y divide-gray-200">
                            @forelse($popular_items as $item)
                            <li class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        @if($item->image)
                                        <img class="object-cover w-10 h-10 rounded-lg" src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
                                        @else
                                        <div class="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-lg">
                                            <i class="text-gray-400 fas fa-utensils"></i>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $item->name }}</p>
                                        <div class="flex items-center mt-1">
                                            <span class="text-sm text-gray-600">MT {{ number_format($item->getFinalPrice(), 2) }}</span>
                                            @if($item->hasDiscount())
                                            <span class="ml-2 text-xs font-medium text-green-600">-{{ $item->getDiscountPercentage() }}%</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 text-right">
                                        <p class="text-sm font-medium text-gray-900">{{ $item->order_items_count ?? 0 }}</p>
                                        <p class="text-sm text-gray-500">pedidos</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $item->is_available ? 'Disponível' : 'Indisponível' }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="px-6 py-8 text-center">
                                <i class="text-3xl text-gray-300 fas fa-utensils"></i>
                                <p class="mt-2 text-sm text-gray-500">Nenhum item no menu ainda</p>
                                <a href="{{ route('restaurant.menu.index') }}" class="inline-flex items-center mt-2 text-sm font-medium text-orange-600 hover:text-orange-500">
                                    Adicionar itens ao menu →
                                </a>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Order Status Modal -->
    <div x-show="orderStatusModal" class="relative z-50" role="dialog" style="display: none;">
        <div x-show="orderStatusModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0">
                <div x-show="orderStatusModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative px-4 pt-5 pb-4 overflow-hidden text-left transform bg-white rounded-lg shadow-xl sm:my-8 sm:w-full sm:max-w-sm sm:p-6">
                    <div>
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-orange-100 rounded-full">
                            <i class="text-orange-600 fas fa-edit"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Atualizar Status do Pedido</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Selecione o novo status para o pedido</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6">
                        <select id="orderStatus" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:text-sm sm:leading-6">
                            <option value="pending">Pendente</option>
                            <option value="confirmed">Confirmado</option>
                            <option value="preparing">Preparando</option>
                            <option value="ready">Pronto para Entrega</option>
                            <option value="cancelled">Cancelar</option>
                        </select>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                        <button type="button"
                                @click="updateOrderStatus()"
                                class="inline-flex justify-center w-full px-3 py-2 text-sm font-semibold text-white bg-orange-600 rounded-md shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600 sm:col-start-2">
                            Atualizar
                        </button>
                        <button type="button"
                                @click="orderStatusModal = false"
                                class="inline-flex justify-center w-full px-3 py-2 mt-3 text-sm font-semibold text-gray-900 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection