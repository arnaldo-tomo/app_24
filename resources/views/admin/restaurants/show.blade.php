@extends('layouts.admin')

@section('content')
<div x-data="{ deleteModal: false }" class="min-h-full">
    <!-- Page content -->
    <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <a href="{{ route('admin.restaurants.index') }}" class="text-gray-400 hover:text-gray-500">
                                <i class="flex-shrink-0 w-5 h-5 fas fa-store"></i>
                                <span class="sr-only">Restaurantes</span>
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="flex-shrink-0 w-5 h-5 text-gray-300 fas fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium text-gray-500">{{ $restaurant->name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                            {{ $restaurant->name }}
                        </h1>
                        <div class="flex flex-col mt-1 sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                            <div class="flex items-center mt-2 text-sm text-gray-500">
                                <i class="flex-shrink-0 mr-1.5 text-gray-400 fas fa-map-marker-alt"></i>
                                {{ Str::limit($restaurant->address, 50) }}
                            </div>
                            <div class="flex items-center mt-2 text-sm text-gray-500">
                                <i class="flex-shrink-0 mr-1.5 text-gray-400 fas fa-phone"></i>
                                {{ $restaurant->phone }}
                            </div>
                            <div class="flex items-center mt-2 text-sm text-gray-500">
                                <i class="flex-shrink-0 mr-1.5 text-gray-400 fas fa-envelope"></i>
                                {{ $restaurant->email }}
                            </div>
                        </div>
                    </div>
                    <div class="flex mt-4 md:ml-4 md:mt-0">
                        <a href="{{ route('admin.restaurants.edit', $restaurant) }}"
                           class="inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-900 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            <i class="mr-2 fas fa-edit"></i>
                            Editar
                        </a>
                        <button @click="deleteModal = true"
                                class="inline-flex items-center px-3 py-2 ml-3 text-sm font-semibold text-white bg-red-600 rounded-md shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                            <i class="mr-2 fas fa-trash"></i>
                            Excluir
                        </button>
                    </div>
                </div>

                <!-- Status badges -->
                <div class="flex flex-wrap gap-2 mt-4">
                    @if($restaurant->is_active)
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

                    @if($restaurant->is_featured)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="mr-1 fas fa-star"></i>
                            Em Destaque
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

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main content -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Restaurant Images -->
                    @if($restaurant->cover_image || $restaurant->image)
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Imagens</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                @if($restaurant->cover_image)
                                <div>
                                    <h3 class="mb-2 text-sm font-medium text-gray-700">Imagem de Capa</h3>
                                    <img src="{{ asset('storage/' . $restaurant->cover_image) }}"
                                         alt="Capa do {{ $restaurant->name }}"
                                         class="object-cover w-full h-48 rounded-lg">
                                </div>
                                @endif

                                @if($restaurant->image)
                                <div>
                                    <h3 class="mb-2 text-sm font-medium text-gray-700">Logo</h3>
                                    <img src="{{ asset('storage/' . $restaurant->image) }}"
                                         alt="Logo do {{ $restaurant->name }}"
                                         class="object-cover w-full h-48 rounded-lg">
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Restaurant Info -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Informações do Restaurante</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nome</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $restaurant->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Proprietário</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $restaurant->owner->name ?? 'N/A' }}
                                        @if($restaurant->owner)
                                            <span class="text-gray-500">({{ $restaurant->owner->email }})</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Telefone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $restaurant->phone }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">E-mail</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $restaurant->email }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Endereço</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $restaurant->address }}</dd>
                                </div>
                                @if($restaurant->description)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Descrição</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $restaurant->description }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Categories -->
                    @if($restaurant->categories->count() > 0)
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Categorias</h2>
                        </div>
                        <div class="p-6">
                            <div class="flex flex-wrap gap-2">
                                @foreach($restaurant->categories as $category)
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-orange-800 bg-orange-100 rounded-full">
                                        {{ $category->icon ?? '🍽️' }} {{ $category->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Delivery Settings -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Configurações de Delivery</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Taxa de Entrega</dt>
                                    <dd class="mt-1 text-sm text-gray-900">MT {{ number_format($restaurant->delivery_fee, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Pedido Mínimo</dt>
                                    <dd class="mt-1 text-sm text-gray-900">MT {{ number_format($restaurant->minimum_order, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tempo de Entrega</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $restaurant->delivery_time_min }} - {{ $restaurant->delivery_time_max }} minutos</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Operating Hours -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Horário de Funcionamento</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Horário</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $restaurant->opening_time }} às {{ $restaurant->closing_time }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Dias de Funcionamento</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($restaurant->working_days)
                                            @php
                                                $dayNames = [
                                                    'monday' => 'Segunda',
                                                    'tuesday' => 'Terça',
                                                    'wednesday' => 'Quarta',
                                                    'thursday' => 'Quinta',
                                                    'friday' => 'Sexta',
                                                    'saturday' => 'Sábado',
                                                    'sunday' => 'Domingo'
                                                ];
                                                $workingDayNames = array_map(function($day) use ($dayNames) {
                                                    return $dayNames[$day] ?? $day;
                                                }, $restaurant->working_days);
                                            @endphp
                                            {{ implode(', ', $workingDayNames) }}
                                        @else
                                            Não informado
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    @if($restaurant->orders && $restaurant->orders->count() > 0)
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Pedidos Recentes</h2>
                        </div>
                        <div class="overflow-hidden">
                            <ul class="divide-y divide-gray-200">
                                @foreach($restaurant->orders as $order)
                                <li class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="flex items-center justify-center w-8 h-8 bg-orange-100 rounded-full">
                                                    <i class="text-orange-600 fas fa-receipt"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Pedido #{{ $order->id }}</div>
                                                <div class="text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-900">
                                            MT {{ number_format($order->total, 2) }}
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
                            <h2 class="text-lg font-medium text-gray-900">Estatísticas</h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Avaliação</dt>
                                    <dd class="flex items-center text-sm text-gray-900">
                                        <i class="mr-1 text-yellow-400 fas fa-star"></i>
                                        {{ number_format($restaurant->rating ?? 0, 1) }}
                                        <span class="ml-1 text-gray-500">({{ $restaurant->total_reviews ?? 0 }})</span>
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Total de Pedidos</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $restaurant->orders_count ?? 0 }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd>
                                        @if($restaurant->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Ativo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Inativo
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Data de Cadastro</dt>
                                    <dd class="text-sm text-gray-900">{{ $restaurant->created_at->format('d/m/Y') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Ações Rápidas</h2>
                        </div>
                        <div class="p-6 space-y-3">
                            <form method="POST" action="{{ route('admin.restaurants.toggle-status', $restaurant) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white rounded-md {{ $restaurant->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                                    <i class="mr-2 fas {{ $restaurant->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    {{ $restaurant->is_active ? 'Desativar' : 'Ativar' }} Restaurante
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.restaurants.toggle-featured', $restaurant) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    <i class="mr-2 fas fa-star"></i>
                                    {{ $restaurant->is_featured ? 'Remover Destaque' : 'Destacar' }}
                                </button>
                            </form>

                            <a href="{{ route('admin.restaurants.edit', $restaurant) }}"
                               class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                <i class="mr-2 fas fa-edit"></i>
                                Editar Informações
                            </a>
                        </div>
                    </div>

                    <!-- Location Info -->
                    @if($restaurant->latitude && $restaurant->longitude)
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Localização</h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Coordenadas</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        Lat: {{ $restaurant->latitude }}<br>
                                        Lng: {{ $restaurant->longitude }}
                                    </dd>
                                </div>
                                <div>
                                    <a href="https://www.google.com/maps?q={{ $restaurant->latitude }},{{ $restaurant->longitude }}"
                                       target="_blank"
                                       class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                        <i class="mr-2 fas fa-map-marker-alt"></i>
                                        Ver no Google Maps
                                    </a>
                                </div>
                            </dl>
                        </div>
                    </div>
                    @endif

                    <!-- Menu Categories -->
                    @if($restaurant->menuCategories && $restaurant->menuCategories->count() > 0)
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Categorias do Menu</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                @foreach($restaurant->menuCategories as $menuCategory)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $menuCategory->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $menuCategory->menuItems->count() }} itens</div>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $menuCategory->is_active ? 'Ativo' : 'Inativo' }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
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
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Excluir Restaurante</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Tem certeza de que deseja excluir o restaurante "{{ $restaurant->name }}"?
                                    Esta ação não pode ser desfeita e todos os dados relacionados serão perdidos.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <form action="{{ route('admin.restaurants.destroy', $restaurant) }}" method="POST" class="inline">
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