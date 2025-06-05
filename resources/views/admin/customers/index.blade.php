@extends('layouts.admin')

@section('content')
<div x-data="{
    viewModal: false,
    selectedCustomer: null,
    selectedCustomerData: null,
    toggleStatus(customerId) {
        fetch(`/admin/customers/${customerId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao alterar status do cliente');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao alterar status do cliente');
        });
    }
}" class="min-h-full">

    <!-- Page content -->
    <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold leading-6 text-gray-900">Clientes</h1>
                    <p class="mt-2 text-sm text-gray-700">Gerencie todos os clientes cadastrados no sistema</p>
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
            <div class="grid grid-cols-1 gap-6 mt-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-blue-500 fas fa-users"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dd class="text-lg font-medium text-gray-900">{{ \App\Models\User::where('role', 'customer')->whereDate('created_at', today())->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mt-8 bg-white rounded-lg shadow">
                <div class="px-4 py-5 sm:p-6">
                    <form method="GET" action="{{ route('admin.customers.index') }}">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Buscar</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="Nome, email ou telefone...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Todos os status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inativo</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Data de Cadastro</label>
                                <select name="date_filter" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Todas as datas</option>
                                    <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Hoje</option>
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

            <!-- Customers Table -->
            <div class="flow-root mt-8">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Cliente</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Contato</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Localização</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Pedidos</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Total Gasto</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Cadastro</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($customers as $customer)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-10 h-10">
                                                    @if($customer->avatar)
                                                        <img class="object-cover w-10 h-10 rounded-full" src="{{ asset('storage/' . $customer->avatar) }}" alt="{{ $customer->name }}">
                                                    @else
                                                        <div class="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full">
                                                            <span class="text-sm font-medium text-gray-700">{{ substr($customer->name, 0, 2) }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                                    <div class="text-sm text-gray-500">ID: {{ $customer->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $customer->email }}</div>
                                            @if($customer->phone)
                                                <div class="text-sm text-gray-500">{{ $customer->phone }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($customer->address)
                                                <div class="text-sm text-gray-900">{{ Str::limit($customer->address, 30) }}</div>
                                                @if($customer->latitude && $customer->longitude)
                                                    <div class="text-xs text-gray-500">
                                                        {{ number_format($customer->latitude, 4) }}, {{ number_format($customer->longitude, 4) }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-sm text-gray-500">Não informado</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $customer->orders_count ?? 0 }}</div>
                                            @if($customer->orders_count > 0)
                                                <div class="text-sm text-gray-500">
                                                    Último: {{ $customer->orders->first()?->created_at?->format('d/m/Y') ?? 'N/A' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $totalSpent = $customer->orders->where('payment_status', 'paid')->sum('total_amount');
                                            @endphp
                                            <div class="text-sm font-medium text-gray-900">MT {{ number_format($totalSpent, 2) }}</div>
                                            @if($totalSpent > 0)
                                                <div class="text-sm text-gray-500">
                                                    Média: MT {{ number_format($totalSpent / max($customer->orders_count, 1), 2) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($customer->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                    Ativo
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                                    Inativo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $customer->created_at->format('d/m/Y') }}</div>
                                            <div class="text-sm text-gray-500">{{ $customer->created_at->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <button @click="
                                                    selectedCustomer = {{ $customer->id }};
                                                    selectedCustomerData = {
                                                        name: '{{ $customer->name }}',
                                                        email: '{{ $customer->email }}',
                                                        phone: '{{ $customer->phone ?? 'Não informado' }}',
                                                        address: '{{ $customer->address ?? 'Não informado' }}',
                                                        created_at: '{{ $customer->created_at->format('d/m/Y H:i') }}',
                                                        orders_count: {{ $customer->orders_count ?? 0 }},
                                                        total_spent: '{{ number_format($totalSpent, 2) }}',
                                                        is_active: {{ $customer->is_active ? 'true' : 'false' }},
                                                        email_verified: {{ $customer->email_verified_at ? 'true' : 'false' }},
                                                        last_order: '{{ $customer->orders->first()?->created_at?->format('d/m/Y H:i') ?? 'Nunca fez pedidos' }}'
                                                    };
                                                    viewModal = true
                                                "
                                                class="text-blue-600 hover:text-blue-900" title="Ver detalhes">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <button @click="toggleStatus({{ $customer->id }})"
                                                        class="{{ $customer->is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}"
                                                        title="{{ $customer->is_active ? 'Desativar' : 'Ativar' }}">
                                                    <i class="fas {{ $customer->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                                </button>

                                                @if($customer->orders_count > 0)
                                                    <a href="{{ route('admin.orders.index', ['customer_id' => $customer->id]) }}"
                                                       class="text-orange-600 hover:text-orange-900" title="Ver pedidos">
                                                        <i class="fas fa-shopping-bag"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <i class="mb-4 text-6xl text-gray-300 fas fa-users"></i>
                                                <h3 class="mb-2 text-lg font-medium text-gray-900">Nenhum cliente encontrado</h3>
                                                <p class="text-gray-500">Não há clientes que correspondam aos filtros selecionados.</p>
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
            @if($customers->hasPages())
            <div class="mt-6">
                {{ $customers->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </main>

    <!-- Customer Details Modal -->
    <div x-show="viewModal" class="relative z-50" role="dialog" style="display: none;">
        <div x-show="viewModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0">
                <div x-show="viewModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     class="relative px-4 pt-5 pb-4 overflow-hidden text-left transform bg-white rounded-lg shadow-xl sm:my-8 sm:w-full sm:max-w-lg sm:p-6">

                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Detalhes do Cliente</h3>
                        <button @click="viewModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div x-show="selectedCustomerData" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedCustomerData?.name"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <p class="mt-1 text-sm">
                                    <span x-show="selectedCustomerData?.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Ativo
                                    </span>
                                    <span x-show="!selectedCustomerData?.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Inativo
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedCustomerData?.email"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Telefone</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedCustomerData?.phone"></p>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Endereço</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedCustomerData?.address"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total de Pedidos</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedCustomerData?.orders_count"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Gasto</label>
                                <p class="mt-1 text-sm text-gray-900">MT <span x-text="selectedCustomerData?.total_spent"></span></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cadastro</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedCustomerData?.created_at"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Último Pedido</label>
                                <p class="mt-1 text-sm text-gray-900" x-text="selectedCustomerData?.last_order"></p>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <div class="flex items-center">
                                <span x-show="selectedCustomerData?.email_verified" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="mr-1 fas fa-check"></i>Email verificado
                                </span>
                                <span x-show="!selectedCustomerData?.email_verified" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="mr-1 fas fa-exclamation-triangle"></i>Email não verificado
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 space-x-3">
                        <button @click="viewModal = false" class="px-3 py-2 text-sm font-semibold text-gray-900 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Fechar
                        </button>
                        <button x-show="selectedCustomerData?.orders_count > 0"
                                @click="window.open(`/admin/orders?customer_id=${selectedCustomer}`, '_blank')"
                                class="px-3 py-2 text-sm font-semibold text-white bg-orange-600 rounded-md shadow-sm hover:bg-orange-500">
                            Ver Pedidos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection