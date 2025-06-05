@extends('layouts.admin')

@section('content')
<div class="min-h-full">
    <!-- Page content -->
    <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="sm:flex sm:items-center sm:justify-between">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold leading-6 text-gray-900">Relatórios e Análises</h1>
                    <p class="mt-2 text-sm text-gray-700">Visualize métricas e insights do seu negócio</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <form method="GET" class="flex items-center space-x-2">
                        <select name="period" onchange="this.form.submit()"
                                class="block border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            <option value="7" {{ $period == '7' ? 'selected' : '' }}>Últimos 7 dias</option>
                            <option value="30" {{ $period == '30' ? 'selected' : '' }}>Últimos 30 dias</option>
                            <option value="90" {{ $period == '90' ? 'selected' : '' }}>Últimos 90 dias</option>
                            <option value="365" {{ $period == '365' ? 'selected' : '' }}>Último ano</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 gap-6 mt-8 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Orders -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-blue-500 fas fa-shopping-cart"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total de Pedidos</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['total_orders']['current']) }}
                                        </div>
                                        @php
                                            $orderGrowth = $stats['total_orders']['previous'] > 0
                                                ? (($stats['total_orders']['current'] - $stats['total_orders']['previous']) / $stats['total_orders']['previous']) * 100
                                                : 0;
                                        @endphp
                                        <div class="ml-2 flex items-baseline text-sm font-semibold {{ $orderGrowth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            <i class="fas {{ $orderGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                                            {{ number_format(abs($orderGrowth), 1) }}%
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-green-500 fas fa-dollar-sign"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Receita Total</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            MT {{ number_format($stats['total_revenue']['current'], 0) }}
                                        </div>
                                        @php
                                            $revenueGrowth = $stats['total_revenue']['previous'] > 0
                                                ? (($stats['total_revenue']['current'] - $stats['total_revenue']['previous']) / $stats['total_revenue']['previous']) * 100
                                                : 0;
                                        @endphp
                                        <div class="ml-2 flex items-baseline text-sm font-semibold {{ $revenueGrowth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            <i class="fas {{ $revenueGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                                            {{ number_format(abs($revenueGrowth), 1) }}%
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Order Value -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-orange-500 fas fa-receipt"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Ticket Médio</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            MT {{ number_format($stats['avg_order_value']['current'], 0) }}
                                        </div>
                                        @php
                                            $avgGrowth = $stats['avg_order_value']['previous'] > 0
                                                ? (($stats['avg_order_value']['current'] - $stats['avg_order_value']['previous']) / $stats['avg_order_value']['previous']) * 100
                                                : 0;
                                        @endphp
                                        <div class="ml-2 flex items-baseline text-sm font-semibold {{ $avgGrowth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            <i class="fas {{ $avgGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                                            {{ number_format(abs($avgGrowth), 1) }}%
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Customers -->
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-purple-500 fas fa-users"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Novos Clientes</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ number_format($stats['new_customers']['current']) }}
                                        </div>
                                        @php
                                            $customerGrowth = $stats['new_customers']['previous'] > 0
                                                ? (($stats['new_customers']['current'] - $stats['new_customers']['previous']) / $stats['new_customers']['previous']) * 100
                                                : 0;
                                        @endphp
                                        <div class="ml-2 flex items-baseline text-sm font-semibold {{ $customerGrowth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            <i class="fas {{ $customerGrowth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                                            {{ number_format(abs($customerGrowth), 1) }}%
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="grid grid-cols-1 gap-6 mt-8 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('admin.reports.sales') }}"
                   class="relative p-6 transition-shadow duration-200 bg-white rounded-lg shadow group hover:shadow-lg">
                    <div>
                        <span class="inline-flex p-3 text-white bg-blue-600 rounded-lg">
                            <i class="text-xl fas fa-chart-line"></i>
                        </span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-lg font-medium text-gray-900 group-hover:text-blue-600">
                            Relatório de Vendas
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Análise detalhada de vendas, produtos mais vendidos e performance por período.
                        </p>
                    </div>
                    <span class="absolute inset-0 rounded-lg pointer-events-none ring-1 ring-inset ring-black ring-opacity-10" aria-hidden="true"></span>
                </a>

                <a href="{{ route('admin.reports.restaurants') }}"
                   class="relative p-6 transition-shadow duration-200 bg-white rounded-lg shadow group hover:shadow-lg">
                    <div>
                        <span class="inline-flex p-3 text-white bg-green-600 rounded-lg">
                            <i class="text-xl fas fa-store"></i>
                        </span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-lg font-medium text-gray-900 group-hover:text-green-600">
                            Performance de Restaurantes
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Métricas de performance, receita e eficiência por restaurante.
                        </p>
                    </div>
                    <span class="absolute inset-0 rounded-lg pointer-events-none ring-1 ring-inset ring-black ring-opacity-10" aria-hidden="true"></span>
                </a>

                <a href="{{ route('admin.reports.deliveries') }}"
                   class="relative p-6 transition-shadow duration-200 bg-white rounded-lg shadow group hover:shadow-lg">
                    <div>
                        <span class="inline-flex p-3 text-white bg-orange-600 rounded-lg">
                            <i class="text-xl fas fa-motorcycle"></i>
                        </span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-lg font-medium text-gray-900 group-hover:text-orange-600">
                            Relatório de Entregas
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Performance dos entregadores, tempos de entrega e eficiência.
                        </p>
                    </div>
                    <span class="absolute inset-0 rounded-lg pointer-events-none ring-1 ring-inset ring-black ring-opacity-10" aria-hidden="true"></span>
                </a>

                <a href="{{ route('admin.reports.customers') }}"
                   class="relative p-6 transition-shadow duration-200 bg-white rounded-lg shadow group hover:shadow-lg">
                    <div>
                        <span class="inline-flex p-3 text-white bg-purple-600 rounded-lg">
                            <i class="text-xl fas fa-users"></i>
                        </span>
                    </div>
                    <div class="mt-4">
                        <h3 class="text-lg font-medium text-gray-900 group-hover:text-purple-600">
                            Análise de Clientes
                        </h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Comportamento dos clientes, retenção e valor do tempo de vida.
                        </p>
                    </div>
                    <span class="absolute inset-0 rounded-lg pointer-events-none ring-1 ring-inset ring-black ring-opacity-10" aria-hidden="true"></span>
                </a>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 gap-8 mt-8 lg:grid-cols-2">
                <!-- Orders & Revenue Chart -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Pedidos e Receita</h3>
                    </div>
                    <div class="p-6">
                        <canvas id="ordersRevenueChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <!-- Orders Status Chart -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Status dos Pedidos</h3>
                    </div>
                    <div class="p-6">
                        <canvas id="orderStatusChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Orders & Revenue Chart
    const ordersRevenueCtx = document.getElementById('ordersRevenueChart').getContext('2d');
    const ordersRevenueData = @json($charts['orders_revenue']);

    new Chart(ordersRevenueCtx, {
        type: 'line',
        data: {
            labels: ordersRevenueData.map(item => item.period),
            datasets: [{
                label: 'Pedidos',
                data: ordersRevenueData.map(item => item.orders),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                yAxisID: 'y'
            }, {
                label: 'Receita (MT)',
                data: ordersRevenueData.map(item => item.revenue),
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Período'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Número de Pedidos'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Receita (MT)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Order Status Chart
    const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
    const orderStatusData = @json($charts['orders_status']);

    new Chart(orderStatusCtx, {
        type: 'doughnut',
        data: {
            labels: orderStatusData.map(item => item.status),
            datasets: [{
                data: orderStatusData.map(item => item.count),
                backgroundColor: [
                    'rgb(34, 197, 94)',   // delivered - green
                    'rgb(59, 130, 246)',  // confirmed - blue
                    'rgb(245, 158, 11)',  // preparing - yellow
                    'rgb(239, 68, 68)',   // cancelled - red
                    'rgb(156, 163, 175)', // pending - gray
                    'rgb(147, 51, 234)',  // picked_up - purple
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return `${context.label}: ${context.raw} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection