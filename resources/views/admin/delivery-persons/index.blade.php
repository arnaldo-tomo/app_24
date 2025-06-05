@extends('layouts.admin')

@section('content')
<div x-data="{ deleteModal: false, selectedDeliveryPerson: null }" class="min-h-full">
    <!-- Page content -->
    <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold leading-6 text-gray-900">Entregadores</h1>
                    <p class="mt-2 text-sm text-gray-700">Gerencie todos os entregadores cadastrados no sistema</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <a href="#" class="block px-3 py-2 text-sm font-semibold text-center text-white bg-orange-600 rounded-md shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600">
                    {{-- <a href="{{ route('admin.delivery-persons.create') }}" class="block px-3 py-2 text-sm font-semibold text-center text-white bg-orange-600 rounded-md shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600"> --}}
                        <i class="mr-2 fas fa-plus"></i>Novo Entregador
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

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 mt-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-orange-500 fas fa-motorcycle"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total de Entregadores</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $delivery_persons->total() }}</dd>
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
                                    <dt class="text-sm font-medium text-gray-500 truncate">Ativos</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ \App\Models\User::where('role', 'delivery_person')->where('is_active', true)->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-blue-500 fas fa-shipping-fast"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Entregas Hoje</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ \App\Models\Order::whereDate('created_at', today())->whereNotNull('delivery_person_id')->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-red-500 fas fa-user-times"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Inativos</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ \App\Models\User::where('role', 'delivery_person')->where('is_active', false)->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mt-8 bg-white rounded-lg shadow">
                <div class="px-4 py-5 sm:p-6">
                    <form method="GET" action="{{ route('admin.delivery-persons.index') }}">
                        <div class="flex flex-wrap gap-4">
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700">Buscar</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="Nome, email ou telefone">
                            </div>
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Todos os status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inativo</option>
                                </select>
                            </div>
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700">Ordenar por</label>
                                <select name="sort" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nome (A-Z)</option>
                                    <option value="deliveries_count" {{ request('sort') == 'deliveries_count' ? 'selected' : '' }}>Número de entregas</option>
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Data de cadastro</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="px-4 py-2 text-white bg-orange-600 rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <i class="mr-2 fas fa-search"></i>Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delivery Persons Table -->
            <div class="flow-root mt-8">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Entregador</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Contato</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Localização</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Entregas</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Cadastro</th>
                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($delivery_persons as $deliveryPerson)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 w-12 h-12">
                                                    @if($deliveryPerson->avatar)
                                                        <img class="object-cover w-12 h-12 rounded-full" src="{{ asset('storage/' . $deliveryPerson->avatar) }}" alt="{{ $deliveryPerson->name }}">
                                                    @else
                                                        <div class="flex items-center justify-center w-12 h-12 bg-gray-200 rounded-full">
                                                            <i class="text-gray-400 fas fa-user"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $deliveryPerson->name }}</div>
                                                    <div class="text-sm text-gray-500">ID: {{ $deliveryPerson->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $deliveryPerson->email }}</div>
                                            <div class="text-sm text-gray-500">{{ $deliveryPerson->phone ?? 'Não informado' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($deliveryPerson->address)
                                                <div class="text-sm text-gray-900">{{ Str::limit($deliveryPerson->address, 30) }}</div>
                                                @if($deliveryPerson->latitude && $deliveryPerson->longitude)
                                                    <div class="text-sm text-gray-500">
                                                        <i class="mr-1 fas fa-map-marker-alt"></i>
                                                        Coordenadas disponíveis
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-sm text-gray-500">Não informado</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $deliveryPerson->deliveries_count ?? 0 }}</div>
                                            <div class="text-sm text-gray-500">entregas realizadas</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($deliveryPerson->is_active)
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
                                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                            {{ $deliveryPerson->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <a href="#"
                                                {{-- <a href="{{ route('admin.delivery-persons.edit', $deliveryPerson) }}" --}}
                                                   class="text-orange-600 hover:text-orange-900" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="#}"
                                                {{-- <a href="{{ route('admin.delivery-persons.show', $deliveryPerson) }}" --}}
                                                   class="text-blue-600 hover:text-blue-900" title="Ver detalhes">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <form method="POST" action="#" class="inline">
                                                {{-- <form method="POST" action="{{ route('admin.delivery-persons.toggle-status', $deliveryPerson) }}" class="inline"> --}}
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="{{ $deliveryPerson->is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}"
                                                            title="{{ $deliveryPerson->is_active ? 'Desativar' : 'Ativar' }}">
                                                        <i class="fas {{ $deliveryPerson->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                                    </button>
                                                </form>

                                                @if($deliveryPerson->latitude && $deliveryPerson->longitude)
                                                    <a href="https://www.google.com/maps?q={{ $deliveryPerson->latitude }},{{ $deliveryPerson->longitude }}"
                                                       target="_blank"
                                                       class="text-blue-600 hover:text-blue-900" title="Ver localização">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </a>
                                                @endif

                                                <button @click="selectedDeliveryPerson = {{ $deliveryPerson->id }}; deleteModal = true"
                                                        class="text-red-600 hover:text-red-900" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <i class="mb-4 text-6xl text-gray-300 fas fa-motorcycle"></i>
                                                <h3 class="mb-2 text-lg font-medium text-gray-900">Nenhum entregador encontrado</h3>
                                                <p class="mb-4 text-gray-500">Comece adicionando o primeiro entregador ao sistema.</p>
                                                <a href="#"
                                                {{-- <a href="{{ route('admin.delivery-persons.create') }}" --}}
                                                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700">
                                                    <i class="mr-2 fas fa-plus"></i>
                                                    Adicionar Entregador
                                                </a>
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
            @if($delivery_persons->hasPages())
            <div class="mt-6">
                {{ $delivery_persons->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </main>

    <!-- Delete Modal -->
    <div x-show="deleteModal" class="relative z-50" role="dialog" style="display: none;">
        <div x-show="deleteModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0">
                <div x-show="deleteModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative px-4 pt-5 pb-4 overflow-hidden text-left transform bg-white rounded-lg shadow-xl sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                            <i class="text-red-600 fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Excluir Entregador</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Tem certeza de que deseja excluir este entregador? Esta ação não pode ser desfeita e todos os dados relacionados serão perdidos.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <form x-bind:action="'/admin/delivery-persons/' + selectedDeliveryPerson" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex justify-center w-full px-3 py-2 text-sm font-semibold text-white bg-red-600 rounded-md shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                Excluir
                            </button>
                        </form>
                        <button type="button" @click="deleteModal = false" class="inline-flex justify-center w-full px-3 py-2 mt-3 text-sm font-semibold text-gray-900 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection