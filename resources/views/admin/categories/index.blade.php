@extends('layouts.admin')

@section('content')
<div x-data="{ deleteModal: false, selectedCategory: null }" class="min-h-full">
    <!-- Page content -->
    <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold leading-6 text-gray-900">Categorias</h1>
                    <p class="mt-2 text-sm text-gray-700">Gerencie todas as categorias de restaurantes do sistema</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <a href="{{ route('admin.categories.create') }}" class="block px-3 py-2 text-sm font-semibold text-center text-white bg-orange-600 rounded-md shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600">
                        <i class="mr-2 fas fa-plus"></i>Nova Categoria
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
                                <i class="text-2xl text-orange-500 fas fa-tags"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total de Categorias</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $categories->total() }}</dd>
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
                                    <dt class="text-sm font-medium text-gray-500 truncate">Ativas</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ \App\Models\Category::where('is_active', true)->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-blue-500 fas fa-store"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Com Restaurantes</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ \App\Models\Category::has('restaurants')->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-red-500 fas fa-times-circle"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Inativas</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ \App\Models\Category::where('is_active', false)->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mt-8 bg-white rounded-lg shadow">
                <div class="px-4 py-5 sm:p-6">
                    <form method="GET" action="{{ route('admin.categories.index') }}">
                        <div class="flex flex-wrap gap-4">
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700">Buscar</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="Nome da categoria">
                            </div>
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Todos os status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativa</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inativa</option>
                                </select>
                            </div>
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700">Ordenar por</label>
                                <select name="sort" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="sort_order" {{ request('sort') == 'sort_order' ? 'selected' : '' }}>Ordem</option>
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nome (A-Z)</option>
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Data de criação</option>
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

            <!-- Categories Grid -->
            <div class="mt-8">
                @if($categories->count() > 0)
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach($categories as $category)
                        <div class="overflow-hidden transition-shadow duration-200 bg-white rounded-lg shadow hover:shadow-md">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($category->image)
                                            <img class="object-cover w-12 h-12 rounded-lg" src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}">
                                        @else
                                            <div class="flex items-center justify-center w-12 h-12 bg-orange-100 rounded-lg">
                                                @if($category->icon)
                                                    <span class="text-2xl">{{ $category->icon }}</span>
                                                @else
                                                    <i class="text-orange-600 fas fa-tag"></i>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0 ml-4">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-medium text-gray-900 truncate">{{ $category->name }}</h3>
                                            @if($category->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                                    Ativa
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                                    Inativa
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-1">
                                            <p class="text-sm text-gray-500">{{ $category->restaurants_count }} restaurante(s)</p>
                                            <p class="text-xs text-gray-400">Ordem: {{ $category->sort_order }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-between mt-4 space-x-2">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.categories.edit', $category) }}"
                                           class="text-orange-600 hover:text-orange-900" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.categories.show', $category) }}"
                                           class="text-blue-600 hover:text-blue-900" title="Ver detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>

                                    <div class="flex space-x-2">
                                        <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="{{ $category->is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}"
                                                    title="{{ $category->is_active ? 'Desativar' : 'Ativar' }}">
                                                <i class="fas {{ $category->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>
                                        </form>

                                        @if($category->restaurants_count == 0)
                                            <button @click="selectedCategory = {{ $category->id }}; deleteModal = true"
                                                    class="text-red-600 hover:text-red-900" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <span class="text-gray-400" title="Não é possível excluir - possui restaurantes vinculados">
                                                <i class="fas fa-trash"></i>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="mb-4 text-6xl text-gray-300 fas fa-tags"></i>
                            <h3 class="mb-2 text-lg font-medium text-gray-900">Nenhuma categoria encontrada</h3>
                            <p class="mb-4 text-gray-500">Comece criando a primeira categoria do sistema.</p>
                            <a href="{{ route('admin.categories.create') }}"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700">
                                <i class="mr-2 fas fa-plus"></i>
                                Criar Categoria
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($categories->hasPages())
            <div class="mt-6">
                {{ $categories->withQueryString()->links() }}
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
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Excluir Categoria</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Tem certeza de que deseja excluir esta categoria? Esta ação não pode ser desfeita.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <form x-bind:action="'/admin/categories/' + selectedCategory" method="POST" class="inline">
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