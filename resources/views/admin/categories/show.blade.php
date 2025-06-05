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
                            <a href="{{ route('admin.categories.index') }}" class="text-gray-400 hover:text-gray-500">
                                <i class="flex-shrink-0 w-5 h-5 fas fa-tags"></i>
                                <span class="sr-only">Categorias</span>
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="flex-shrink-0 w-5 h-5 text-gray-300 fas fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium text-gray-500">{{ $category->name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($category->image)
                                    <img class="object-cover w-16 h-16 rounded-lg" src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}">
                                @else
                                    <div class="flex items-center justify-center w-16 h-16 bg-orange-100 rounded-lg">
                                        @if($category->icon)
                                            <span class="text-3xl">{{ $category->icon }}</span>
                                        @else
                                            <i class="text-2xl text-orange-600 fas fa-tag"></i>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                                    {{ $category->name }}
                                </h1>
                                <div class="flex items-center mt-1">
                                    @if($category->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                            Ativa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-2">
                                            <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                            Inativa
                                        </span>
                                    @endif
                                    <span class="text-sm text-gray-500">Ordem: {{ $category->sort_order }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex mt-4 md:ml-4 md:mt-0">
                        <a href="{{ route('admin.categories.edit', $category) }}"
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
                    <!-- Category Info -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Informações da Categoria</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nome</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $category->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Slug</dt>
                                    <dd class="px-2 py-1 mt-1 font-mono text-sm text-gray-900 bg-gray-100 rounded">{{ $category->slug }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Ícone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($category->icon)
                                            <span class="text-2xl">{{ $category->icon }}</span>
                                        @else
                                            <span class="text-gray-400">Não definido</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Ordem de Exibição</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $category->sort_order }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Data de Criação</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $category->created_at->format('d/m/Y H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Última Atualização</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $category->updated_at->format('d/m/Y H:i') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Restaurants in Category -->
                    @if($category->restaurants->count() > 0)
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Restaurantes nesta Categoria</h2>
                        </div>
                        <div class="overflow-hidden">
                            <ul class="divide-y divide-gray-200">
                                @foreach($category->restaurants->take(10) as $restaurant)
                                <li class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                @if($restaurant->image)
                                                    <img class="object-cover w-12 h-12 rounded-lg" src="{{ asset('storage/' . $restaurant->image) }}" alt="{{ $restaurant->name }}">
                                                @else
                                                    <div class="flex items-center justify-center w-12 h-12 bg-gray-200 rounded-lg">
                                                        <i class="text-gray-400 fas fa-store"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $restaurant->name }}</div>
                                                <div class="text-sm text-gray-500">{{ Str::limit($restaurant->address, 40) }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <div class="text-right">
                                                <div class="text-sm font-medium text-gray-900">{{ $restaurant->orders_count ?? 0 }} pedidos</div>
                                                <div class="text-sm text-gray-500">
                                                    @if($restaurant->rating)
                                                        <i class="text-yellow-400 fas fa-star"></i>
                                                        {{ number_format($restaurant->rating, 1) }}
                                                    @else
                                                        Sem avaliações
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                @if($restaurant->is_active)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Ativo
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Inativo
                                                    </span>
                                                @endif
                                            </div>
                                            <a href="{{ route('admin.restaurants.show', $restaurant) }}"
                                               class="text-orange-600 hover:text-orange-900">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>

                            @if($category->restaurants->count() > 10)
                                <div class="px-6 py-3 border-t border-gray-200 bg-gray-50">
                                    <p class="text-sm text-center text-gray-500">
                                        E mais {{ $category->restaurants->count() - 10 }} restaurante(s)...
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Restaurantes nesta Categoria</h2>
                        </div>
                        <div class="p-6 text-center">
                            <i class="mx-auto mb-4 text-6xl text-gray-300 fas fa-store"></i>
                            <h3 class="mb-2 text-lg font-medium text-gray-900">Nenhum restaurante</h3>
                            <p class="text-gray-500">Esta categoria ainda não possui restaurantes vinculados.</p>
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
                                    <dt class="text-sm font-medium text-gray-500">Total de Restaurantes</dt>
                                    <dd class="text-2xl font-bold text-orange-600">{{ $category->restaurants->count() }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Restaurantes Ativos</dt>
                                    <dd class="text-2xl font-bold text-green-600">{{ $category->restaurants->where('is_active', true)->count() }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd>
                                        @if($category->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Ativa
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Inativa
                                            </span>
                                        @endif
                                    </dd>
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
                            <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white rounded-md {{ $category->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                                    <i class="mr-2 fas {{ $category->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    {{ $category->is_active ? 'Desativar' : 'Ativar' }} Categoria
                                </button>
                            </form>

                            <a href="{{ route('admin.categories.edit', $category) }}"
                               class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                <i class="mr-2 fas fa-edit"></i>
                                Editar Informações
                            </a>

                            <a href="{{ route('admin.categories.index') }}?search={{ $category->name }}"
                               class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                <i class="mr-2 fas fa-search"></i>
                                Ver Categorias Similares
                            </a>
                        </div>
                    </div>

                    <!-- Category Preview -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Preview da Categoria</h2>
                        </div>
                        <div class="p-6">
                            <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <p class="mb-3 text-sm font-medium text-gray-700">Como aparece no app:</p>
                                <div class="inline-flex items-center px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm">
                                    <div class="flex items-center justify-center w-10 h-10 mr-3 bg-orange-100 rounded-lg">
                                        @if($category->icon)
                                            <span class="text-xl">{{ $category->icon }}</span>
                                        @else
                                            <i class="text-orange-600 fas fa-tag"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $category->restaurants->count() }} restaurante(s)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Excluir Categoria</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Tem certeza de que deseja excluir a categoria "{{ $category->name }}"?
                                    @if($category->restaurants->count() > 0)
                                        <strong class="text-red-600">Esta categoria possui {{ $category->restaurants->count() }} restaurante(s) vinculado(s) e não pode ser excluída.</strong>
                                    @else
                                        Esta ação não pode ser desfeita.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        @if($category->restaurants->count() == 0)
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex justify-center w-full px-3 py-2 text-sm font-semibold text-white bg-red-600 rounded-md shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                    Excluir
                                </button>
                            </form>
                        @endif
                        <button type="button" @click="deleteModal = false" class="inline-flex justify-center w-full px-3 py-2 mt-3 text-sm font-semibold text-gray-900 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            {{ $category->restaurants->count() > 0 ? 'Fechar' : 'Cancelar' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection