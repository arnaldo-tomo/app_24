@extends('layouts.restaurant')

@section('content')
<div x-data="{
    deleteModal: false,
    selectedItem: null,
    addCategoryModal: false,
    addItemModal: false,
    selectedCategory: null
}" class="min-h-full">

    <!-- Page content -->
    <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold leading-6 text-gray-900">Menu do {{ $restaurant->name }}</h1>
                    <p class="mt-2 text-sm text-gray-700">Gerencie as categorias e itens do seu menu</p>
                </div>
                <div class="mt-4 space-x-3 sm:ml-16 sm:mt-0 sm:flex-none">
                    <button @click="addCategoryModal = true" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-900 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <i class="mr-2 fas fa-folder-plus"></i>Nova Categoria
                    </button>
                    <a href="{{ route('restaurant.menu.items.create', $menu_categories->first() ?? 0) }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-white bg-orange-600 rounded-md shadow-sm hover:bg-orange-500">
                        <i class="mr-2 fas fa-plus"></i>Novo Item
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
            <div class="grid grid-cols-1 gap-6 mt-8 sm:grid-cols-4">
                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-orange-500 fas fa-utensils"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total de Itens</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $restaurant->menuItems->count() }}</dd>
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
                                    <dt class="text-sm font-medium text-gray-500 truncate">Disponíveis</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $restaurant->menuItems->where('is_available', true)->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-blue-500 fas fa-folder"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Categorias</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $menu_categories->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white rounded-lg shadow">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="text-2xl text-yellow-500 fas fa-star"></i>
                            </div>
                            <div class="flex-1 w-0 ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Em Destaque</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $restaurant->menuItems->where('is_featured', true)->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Categories and Items -->
            <div class="mt-8 space-y-8">
                @forelse($menu_categories as $category)
                <div class="bg-white rounded-lg shadow">
                    <!-- Category Header -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ $category->name }}</h3>
                                @if($category->description)
                                    <p class="text-sm text-gray-500">{{ $category->description }}</p>
                                @endif
                                <p class="mt-1 text-xs text-gray-400">{{ $category->menuItems->count() }} itens</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('restaurant.menu.items.create', $category) }}" class="text-sm font-medium text-orange-600 hover:text-orange-900">
                                    <i class="mr-1 fas fa-plus"></i>Adicionar Item
                                </a>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Category Items -->
                    <div class="p-6">
                        @if($category->menuItems->count() > 0)
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($category->menuItems as $item)
                            <div class="overflow-hidden transition-shadow border border-gray-200 rounded-lg hover:shadow-md">
                                <!-- Item Image -->
                                <div class="bg-gray-200 aspect-w-16 aspect-h-9">
                                    @if($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="object-cover w-full h-32">
                                    @else
                                        <div class="flex items-center justify-center w-full h-32 bg-gray-100">
                                            <i class="text-2xl text-gray-400 fas fa-utensils"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Item Details -->
                                <div class="p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-base font-medium text-gray-900">{{ $item->name }}</h4>
                                            @if($item->description)
                                                <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ Str::limit($item->description, 80) }}</p>
                                            @endif
                                        </div>
                                        <div class="flex flex-col items-end ml-3">
                                            @if($item->hasDiscount())
                                                <span class="text-sm text-gray-400 line-through">MT {{ number_format($item->price, 2) }}</span>
                                                <span class="text-lg font-bold text-green-600">MT {{ number_format($item->discount_price, 2) }}</span>
                                                <span class="px-2 py-1 text-xs text-red-800 bg-red-100 rounded-full">-{{ $item->getDiscountPercentage() }}%</span>
                                            @else
                                                <span class="text-lg font-bold text-gray-900">MT {{ number_format($item->price, 2) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Item Features -->
                                    <div class="flex flex-wrap gap-1 mt-3">
                                        @if($item->is_vegetarian)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                                🌱 Vegetariano
                                            </span>
                                        @endif
                                        @if($item->is_vegan)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                                🌿 Vegano
                                            </span>
                                        @endif
                                        @if($item->is_spicy)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                                                🌶️ Picante
                                            </span>
                                        @endif
                                        @if($item->is_featured)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">
                                                ⭐ Destaque
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Item Meta -->
                                    <div class="flex items-center justify-between mt-3 text-sm text-gray-500">
                                        <span>⏱️ {{ $item->preparation_time }} min</span>
                                        @if($item->calories)
                                            <span>🔥 {{ $item->calories }} cal</span>
                                        @endif
                                    </div>

                                    <!-- Item Status & Actions -->
                                    <div class="flex items-center justify-between mt-4">
                                        <div class="flex items-center space-x-2">
                                            <form method="POST" action="{{ route('restaurant.menu.items.toggle-availability', $item) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="flex items-center space-x-2">
                                                    @if($item->is_available)
                                                        <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                                                        <span class="text-sm font-medium text-green-700">Disponível</span>
                                                    @else
                                                        <span class="w-2 h-2 bg-red-400 rounded-full"></span>
                                                        <span class="text-sm font-medium text-red-700">Indisponível</span>
                                                    @endif
                                                </button>
                                            </form>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('restaurant.menu.items.edit', $item) }}"
                                               class="text-orange-600 hover:text-orange-900" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button @click="selectedItem = {{ $item->id }}; deleteModal = true"
                                                    class="text-red-600 hover:text-red-900" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="py-8 text-center">
                            <i class="mb-4 text-4xl text-gray-300 fas fa-utensils"></i>
                            <h4 class="mb-2 text-lg font-medium text-gray-900">Nenhum item nesta categoria</h4>
                            <p class="mb-4 text-gray-500">Adicione o primeiro item a esta categoria.</p>
                            <a href="{{ route('restaurant.menu.items.create', $category) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700">
                                <i class="mr-2 fas fa-plus"></i>
                                Adicionar Item
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-lg shadow">
                    <div class="py-12 text-center">
                        <i class="mb-4 text-6xl text-gray-300 fas fa-folder-open"></i>
                        <h3 class="mb-2 text-lg font-medium text-gray-900">Nenhuma categoria criada</h3>
                        <p class="mb-6 text-gray-500">Comece criando categorias para organizar seu menu.</p>
                        <button @click="addCategoryModal = true"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700">
                            <i class="mr-2 fas fa-folder-plus"></i>
                            Criar Primeira Categoria
                        </button>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </main>

    <!-- Add Category Modal -->
    <div x-show="addCategoryModal" class="relative z-50" role="dialog" style="display: none;">
        <div x-show="addCategoryModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0">
                <div x-show="addCategoryModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     class="relative px-4 pt-5 pb-4 overflow-hidden text-left transform bg-white rounded-lg shadow-xl sm:my-8 sm:w-full sm:max-w-lg sm:p-6">

                    <form method="POST" action="{{ route('restaurant.menu.categories.store') }}">
                        @csrf
                        <div>
                            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-orange-100 rounded-full">
                                <i class="text-orange-600 fas fa-folder-plus"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-5">
                                <h3 class="text-base font-semibold leading-6 text-gray-900">Nova Categoria</h3>
                                <div class="mt-4 space-y-4">
                                    <div class="text-left">
                                        <label for="category_name" class="block text-sm font-medium text-gray-700">Nome da Categoria</label>
                                        <input type="text" name="name" id="category_name" required
                                               class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                               placeholder="Ex: Pizzas, Bebidas, Sobremesas">
                                    </div>
                                    <div class="text-left">
                                        <label for="category_description" class="block text-sm font-medium text-gray-700">Descrição (opcional)</label>
                                        <textarea name="description" id="category_description" rows="3"
                                                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                                  placeholder="Descrição da categoria..."></textarea>
                                    </div>
                                    <div class="text-left">
                                        <label for="category_sort_order" class="block text-sm font-medium text-gray-700">Ordem de Exibição</label>
                                        <input type="number" name="sort_order" id="category_sort_order" min="0" value="0"
                                               class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="submit" class="inline-flex justify-center w-full px-3 py-2 text-sm font-semibold text-white bg-orange-600 rounded-md shadow-sm hover:bg-orange-500 sm:col-start-2">
                                Criar Categoria
                            </button>
                            <button type="button" @click="addCategoryModal = false" class="inline-flex justify-center w-full px-3 py-2 mt-3 text-sm font-semibold text-gray-900 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Item Modal -->
    <div x-show="deleteModal" class="relative z-50" role="dialog" style="display: none;">
        <div x-show="deleteModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0">
                <div x-show="deleteModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     class="relative px-4 pt-5 pb-4 overflow-hidden text-left transform bg-white rounded-lg shadow-xl sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                            <i class="text-red-600 fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Excluir Item do Menu</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Tem certeza de que deseja excluir este item? Esta ação não pode ser desfeita.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <form x-bind:action="'/restaurant/menu/items/' + selectedItem" method="POST" class="inline">
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