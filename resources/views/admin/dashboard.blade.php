

@extends('layouts.admin')

@section('content')
<main class="py-10">
    <div class="px-4 sm:px-6 lg:px-8">
        <!-- Dashboard header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-2 text-sm text-gray-700">Visão geral do seu sistema de delivery</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Orders -->
            <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-8 h-8 bg-orange-100 rounded-lg">
                                <i class="text-orange-600 fas fa-shopping-bag"></i>
                            </div>
                        </div>
                        <div class="flex-1 w-0 ml-4">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total de Pedidos</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $stats['total_orders'] }}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center text-sm text-green-600">
                            <i class="mr-1 fas fa-arrow-up"></i>
                            <span>{{ $stats['orders_today'] }} hoje</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue -->
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
                                <dt class="text-sm font-medium text-gray-500 truncate">Receita Total</dt>
                                <dd class="text-2xl font-bold text-gray-900">MT {{
                                    number_format($stats['total_revenue'], 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center text-sm text-green-600">
                            <i class="mr-1 fas fa-arrow-up"></i>
                            <span>MT {{ number_format($stats['revenue_today'], 2) }} hoje</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Restaurants -->
            <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-lg">
                                <i class="text-blue-600 fas fa-store"></i>
                            </div>
                        </div>
                        <div class="flex-1 w-0 ml-4">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Restaurantes Ativos</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $stats['active_restaurants'] }}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center text-sm text-blue-600">
                            <i class="mr-1 fas fa-store"></i>
                            <span>{{ \App\Models\Restaurant::count() }} total</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Personnel -->
            <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-8 h-8 bg-purple-100 rounded-lg">
                                <i class="text-purple-600 fas fa-motorcycle"></i>
                            </div>
                        </div>
                        <div class="flex-1 w-0 ml-4">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Entregadores Online</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $stats['online_delivery_persons'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="mr-1 fas fa-users"></i>
                            <span>{{ \App\Models\User::where('role', 'delivery_person')->count() }} total</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Recent Activity -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <!-- Recent Orders -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Pedidos Recentes</h3>
                </div>
                <div class="flow-root">
                    <ul role="list" class="divide-y divide-gray-200">
                        @forelse($recent_orders as $order)
                        <li class="px-6 py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-8 h-8 bg-orange-100 rounded-full">
                                        <span class="text-sm font-medium text-orange-800">#{{
                                            substr($order->order_number, -3) }}</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $order->customer->name }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">{{ $order->restaurant->name }} - {{
                                        $order->items->count() }} itens</p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <p class="text-sm font-medium text-gray-900">MT {{
                                        number_format($order->total_amount, 2) }}</p>
                                    <p class="text-sm">
                                        @switch($order->status)
                                        @case('pending')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pendente</span>
                                        @break
                                        @case('confirmed')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Confirmado</span>
                                        @break
                                        @case('preparing')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Preparando</span>
                                        @break
                                        @case('ready')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Pronto</span>
                                        @break
                                        @case('picked_up')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">A
                                            caminho</span>
                                        @break
                                        @case('delivered')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Entregue</span>
                                        @break
                                        @case('cancelled')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelado</span>
                                        @break
                                        @default
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{
                                            ucfirst($order->status) }}</span>
                                        @endswitch
                                    </p>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="px-6 py-8 text-center">
                            <i class="text-3xl text-gray-300 fas fa-store"></i>
                            <p class="mt-2 text-sm text-gray-500">Nenhum restaurante cadastrado</p>
                            <a href="{{ route('admin.restaurants.create') }}"
                                class="inline-flex items-center mt-2 text-sm font-medium text-orange-600 hover:text-orange-500">
                                Adicionar primeiro restaurante →
                            </a>
                        </li>
                        @endforelse
                    </ul>
                </div>
                <div class="px-6 py-3 border-t border-gray-200">
                    <a href="{{ route('admin.restaurants.index') }}"
                        class="text-sm font-medium text-orange-600 hover:text-orange-500">Ver todos os restaurantes
                        →</a>
                </div>
            </div>
               <!-- Top Restaurants -->
               <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Restaurantes Mais Populares</h3>
                </div>
                <div class="flow-root">
                    <ul role="list" class="divide-y divide-gray-200">
                        <li class="px-6 py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <img class="object-cover w-10 h-10 rounded-lg"
                                        src="https://images.unsplash.com/photo-1571997478779-2adcbbe9ab2f?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&h=100&q=80"
                                        alt="Restaurant">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">Bella Vista
                                        Pizzaria</p>
                                    <div class="flex items-center mt-1">
                                        <div class="flex items-center">
                                            <i class="text-xs text-yellow-400 fas fa-star"></i>
                                            <span class="ml-1 text-sm text-gray-600">4.8</span>
                                        </div>
                                        <span class="mx-2 text-gray-300">•</span>
                                        <span class="text-sm text-gray-600">47 pedidos hoje</span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Aberto
                                    </span>
                                </div>
                            </div>
                        </li>
                        <li class="px-6 py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <img class="object-cover w-10 h-10 rounded-lg"
                                        src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&h=100&q=80"
                                        alt="Restaurant">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">Burger House</p>
                                    <div class="flex items-center mt-1">
                                        <div class="flex items-center">
                                            <i class="text-xs text-yellow-400 fas fa-star"></i>
                                            <span class="ml-1 text-sm text-gray-600">4.6</span>
                                        </div>
                                        <span class="mx-2 text-gray-300">•</span>
                                        <span class="text-sm text-gray-600">32 pedidos hoje</span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Aberto
                                    </span>
                                </div>
                            </div>
                        </li>
                        <li class="px-6 py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <img class="object-cover w-10 h-10 rounded-lg"
                                        src="https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&h=100&q=80"
                                        alt="Restaurant">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">Sabor & Tradição
                                    </p>
                                    <div class="flex items-center mt-1">
                                        <div class="flex items-center">
                                            <i class="text-xs text-yellow-400 fas fa-star"></i>
                                            <span class="ml-1 text-sm text-gray-600">4.7</span>
                                        </div>
                                        <span class="mx-2 text-gray-300">•</span>
                                        <span class="text-sm text-gray-600">28 pedidos hoje</span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Fechado
                                    </span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="px-6 py-3 border-t border-gray-200">
                    <a href="#" class="text-sm font-medium text-orange-600 hover:text-orange-500">Ver todos
                        os restaurantes →</a>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

