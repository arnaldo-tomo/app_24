@extends('layouts.admin')

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
                        <li>
                            <div class="flex items-center">
                                <i class="flex-shrink-0 w-5 h-5 text-gray-300 fas fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium text-gray-500">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold leading-6 text-gray-900">Editar Categoria</h1>
                <p class="mt-2 text-sm text-gray-700">Atualize as informações da categoria {{ $category->name }}</p>
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

            <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Informações Básicas -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Informações da Categoria</h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nome da Categoria *</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="Ex: Pizza, Hambúrguer, Sushi">
                            </div>

                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700">Ordem de Exibição</label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="0">
                                <p class="mt-1 text-xs text-gray-500">Menor número aparece primeiro</p>
                            </div>
                        </div>

                        <div>
                            <label for="icon" class="block text-sm font-medium text-gray-700">Ícone (Emoji)</label>
                            <div class="relative mt-1">
                                <input type="text" name="icon" id="icon" value="{{ old('icon', $category->icon) }}"
                                       class="block w-full pr-12 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="🍕"
                                       maxlength="2">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <span class="text-gray-400 sm:text-sm">
                                        <i class="fas fa-smile"></i>
                                    </span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Use um emoji para representar a categoria (ex: 🍕, 🍔, 🍣)</p>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                            <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
                                Categoria Ativa
                            </label>
                            <p class="ml-2 text-xs text-gray-500">Categorias ativas são exibidas no sistema</p>
                        </div>
                    </div>
                </div>

                <!-- Imagem da Categoria -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Imagem da Categoria</h2>
                    </div>
                    <div class="px-6 py-6">
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700">Imagem</label>

                            @if($category->image)
                                <div class="mb-4">
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="Imagem atual"
                                         class="object-cover w-32 h-32 border border-gray-300 rounded-lg">
                                    <p class="mt-2 text-sm text-gray-500">Imagem atual</p>
                                </div>
                            @endif

                            <div class="flex justify-center px-6 pt-5 pb-6 mt-1 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <i class="mx-auto text-gray-400 fas fa-image fa-3x"></i>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="image" class="relative font-medium text-orange-600 bg-white rounded-md cursor-pointer hover:text-orange-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-orange-500">
                                            <span>{{ $category->image ? 'Alterar imagem' : 'Upload de arquivo' }}</span>
                                            <input id="image" name="image" type="file" accept="image/*" class="sr-only">
                                        </label>
                                        <p class="pl-1">ou arraste e solte</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF, SVG até 2MB</p>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                A imagem será redimensionada automaticamente. Recomendamos imagens quadradas (1:1) para melhor visualização.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas da Categoria -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Estatísticas</h2>
                    </div>
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600">{{ $category->restaurants->count() }}</div>
                                <div class="text-sm text-gray-500">Restaurantes</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $category->created_at->format('d/m/Y') }}</div>
                                <div class="text-sm text-gray-500">Data de Criação</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $category->updated_at->format('d/m/Y') }}</div>
                                <div class="text-sm text-gray-500">Última Atualização</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview da Categoria -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Preview</h2>
                    </div>
                    <div class="px-6 py-6">
                        <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <p class="mb-3 text-sm font-medium text-gray-700">Como a categoria aparecerá:</p>
                            <div class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm">
                                <div class="flex items-center justify-center w-8 h-8 mr-3 bg-orange-100 rounded-lg">
                                    <span id="preview-icon" class="text-lg">{{ $category->icon ?? '🍽️' }}</span>
                                </div>
                                <span id="preview-name" class="text-sm font-medium text-gray-900">{{ $category->name }}</span>
                                <span id="preview-status" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <span class="w-1.5 h-1.5 {{ $category->is_active ? 'bg-green-400' : 'bg-red-400' }} rounded-full mr-1.5"></span>
                                    {{ $category->is_active ? 'Ativa' : 'Inativa' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.categories.index') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Cancelar
                    </a>
                    <a href="{{ route('admin.categories.show', $category) }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <i class="mr-2 fas fa-eye"></i>
                        Ver Detalhes
                    </a>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <i class="mr-2 fas fa-save"></i>
                        Atualizar Categoria
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const iconInput = document.getElementById('icon');
    const isActiveInput = document.getElementById('is_active');
    const previewName = document.getElementById('preview-name');
    const previewIcon = document.getElementById('preview-icon');
    const previewStatus = document.getElementById('preview-status');

    function updatePreview() {
        // Update name
        const name = nameInput.value || 'Nome da Categoria';
        previewName.textContent = name;

        // Update icon
        const icon = iconInput.value || '🍽️';
        previewIcon.textContent = icon;

        // Update status
        const isActive = isActiveInput.checked;
        if (isActive) {
            previewStatus.className = 'ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
            previewStatus.innerHTML = '<span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>Ativa';
        } else {
            previewStatus.className = 'ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
            previewStatus.innerHTML = '<span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>Inativa';
        }
    }

    // Add event listeners
    nameInput.addEventListener('input', updatePreview);
    iconInput.addEventListener('input', updatePreview);
    isActiveInput.addEventListener('change', updatePreview);

    // Image preview
    const imageInput = document.getElementById('image');
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('img');
                preview.src = e.target.result;
                preview.className = 'w-32 h-32 mx-auto mt-4 object-cover rounded-lg border border-gray-300';

                // Remove existing preview
                const existingPreview = document.querySelector('.image-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }

                // Add new preview
                const uploadArea = imageInput.closest('.border-dashed');
                preview.classList.add('image-preview');
                uploadArea.appendChild(preview);
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endsection