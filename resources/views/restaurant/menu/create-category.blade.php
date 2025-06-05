@extends('layouts.restaurant')

@section('content')
<div class="min-h-full">
    <!-- Page content -->
    <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <a href="{{ route('restaurant.menu.index') }}" class="text-gray-400 hover:text-gray-500">
                                <i class="flex-shrink-0 w-5 h-5 fas fa-list"></i>
                                <span class="sr-only">Menu</span>
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="flex-shrink-0 w-5 h-5 text-gray-300 fas fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium text-gray-500">Nova Categoria</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold leading-6 text-gray-900">Criar Nova Categoria</h1>
                <p class="mt-2 text-sm text-gray-700">Adicione uma nova categoria ao menu do {{ $restaurant->name }}</p>
            </div>

            @if ($errors->any())
                <div class="p-4 mb-6 rounded-md bg-red-50">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="text-red-400 fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Existem erros no formulário:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="pl-5 space-y-1 list-disc">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('restaurant.menu.store-category') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Informações da Categoria -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Informações da Categoria</h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nome da Categoria *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="Ex: Entradas, Pratos Principais, Bebidas">
                            </div>

                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700">Ordem de Exibição</label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="0">
                                <p class="mt-1 text-xs text-gray-500">Menor número aparece primeiro no menu</p>
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição (Opcional)</label>
                            <textarea name="description" id="description" rows="3"
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                      placeholder="Descrição da categoria que aparecerá no menu">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Preview da Categoria -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Preview da Categoria</h2>
                    </div>
                    <div class="px-6 py-6">
                        <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <p class="mb-3 text-sm font-medium text-gray-700">Como a categoria aparecerá no menu:</p>
                            <div class="p-4 bg-white border border-gray-300 rounded-lg">
                                <div class="pb-3 mb-3 border-b border-gray-200">
                                    <h3 id="preview-name" class="text-lg font-medium text-gray-900">Nome da Categoria</h3>
                                    <p id="preview-description" class="mt-1 text-sm text-gray-500">Descrição da categoria</p>
                                </div>
                                <div class="py-8 text-center text-gray-400">
                                    <i class="mb-2 text-4xl fas fa-utensils"></i>
                                    <p class="text-sm">Os itens do menu aparecerão aqui</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exemplos de Categorias -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Sugestões de Categorias</h2>
                    </div>
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                            @php
                                $suggestions = [
                                    ['name' => 'Entradas', 'description' => 'Aperitivos e petiscos para começar'],
                                    ['name' => 'Pratos Principais', 'description' => 'Nossos principais pratos'],
                                    ['name' => 'Sobremesas', 'description' => 'Doces e sobremesas deliciosas'],
                                    ['name' => 'Bebidas', 'description' => 'Sucos, refrigerantes e bebidas'],
                                    ['name' => 'Pizzas', 'description' => 'Pizzas tradicionais e especiais'],
                                    ['name' => 'Hambúrgueres', 'description' => 'Hambúrgueres artesanais'],
                                    ['name' => 'Saladas', 'description' => 'Saladas frescas e saudáveis'],
                                    ['name' => 'Cafés', 'description' => 'Cafés especiais e bebidas quentes']
                                ];
                            @endphp

                            @foreach($suggestions as $suggestion)
                            <button type="button"
                                    onclick="fillSuggestion('{{ $suggestion['name'] }}', '{{ $suggestion['description'] }}')"
                                    class="p-3 text-left transition-colors duration-200 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50">
                                <div class="text-sm font-medium text-gray-900">{{ $suggestion['name'] }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $suggestion['description'] }}</div>
                            </button>
                            @endforeach
                        </div>
                        <p class="mt-4 text-xs text-gray-500">
                            <i class="mr-1 fas fa-lightbulb"></i>
                            Clique em qualquer sugestão para preencher automaticamente os campos
                        </p>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('restaurant.menu.index') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <i class="mr-2 fas fa-save"></i>
                        Criar Categoria
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');

    function updatePreview() {
        const name = nameInput.value || 'Nome da Categoria';
        const description = descriptionInput.value || 'Descrição da categoria';

        previewName.textContent = name;
        previewDescription.textContent = description;
    }

    // Add event listeners
    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);

    // Initial preview update
    updatePreview();
});

function fillSuggestion(name, description) {
    document.getElementById('name').value = name;
    document.getElementById('description').value = description;

    // Update preview
    document.getElementById('preview-name').textContent = name;
    document.getElementById('preview-description').textContent = description;
}
</script>
@endsection