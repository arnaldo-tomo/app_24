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
                                <span class="ml-4 text-sm font-medium text-gray-500">{{ $menuCategory->name }}</span>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="flex-shrink-0 w-5 h-5 text-gray-300 fas fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium text-gray-500">Novo Item</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold leading-6 text-gray-900">Adicionar Item ao Menu</h1>
                <p class="mt-2 text-sm text-gray-700">Adicione um novo item à categoria "{{ $menuCategory->name }}"</p>
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

            <form action="{{ route('restaurant.menu.store-item', $menuCategory) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- Informações Básicas -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Informações Básicas</h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nome do Item *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="Ex: Pizza Margherita, Hambúrguer Especial">
                            </div>

                            <div>
                                <label for="preparation_time" class="block text-sm font-medium text-gray-700">Tempo de Preparo (minutos) *</label>
                                <input type="number" name="preparation_time" id="preparation_time" value="{{ old('preparation_time') }}" required min="1"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="15">
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" id="description" rows="3"
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                      placeholder="Descrição detalhada do item, ingredientes principais, etc.">{{ old('description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">Preço (MT) *</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">MT</span>
                                    </div>
                                    <input type="number" name="price" id="price" value="{{ old('price') }}" required min="0" step="0.01"
                                           class="block w-full pl-12 border-gray-300 rounded-md focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                           placeholder="0.00">
                                </div>
                            </div>

                            <div>
                                <label for="discount_price" class="block text-sm font-medium text-gray-700">Preço Promocional (MT)</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">MT</span>
                                    </div>
                                    <input type="number" name="discount_price" id="discount_price" value="{{ old('discount_price') }}" min="0" step="0.01"
                                           class="block w-full pl-12 border-gray-300 rounded-md focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                           placeholder="0.00">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Opcional - deixe vazio se não houver promoção</p>
                            </div>

                            <div>
                                <label for="calories" class="block text-sm font-medium text-gray-700">Calorias</label>
                                <input type="number" name="calories" id="calories" value="{{ old('calories') }}" min="0"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="250">
                            </div>
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700">Ordem de Exibição</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                   class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                   placeholder="0">
                            <p class="mt-1 text-xs text-gray-500">Menor número aparece primeiro na categoria</p>
                        </div>
                    </div>
                </div>

                <!-- Características do Item -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Características</h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_vegetarian" id="is_vegetarian" value="1"
                                       {{ old('is_vegetarian') ? 'checked' : '' }}
                                       class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                <label for="is_vegetarian" class="ml-2 text-sm font-medium text-gray-700">
                                    🌱 Vegetariano
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_vegan" id="is_vegan" value="1"
                                       {{ old('is_vegan') ? 'checked' : '' }}
                                       class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                <label for="is_vegan" class="ml-2 text-sm font-medium text-gray-700">
                                    🌿 Vegano
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_spicy" id="is_spicy" value="1"
                                       {{ old('is_spicy') ? 'checked' : '' }}
                                       class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                <label for="is_spicy" class="ml-2 text-sm font-medium text-gray-700">
                                    🌶️ Picante
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ingredientes e Alérgenos -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Ingredientes e Alérgenos</h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div>
                            <label for="ingredients_input" class="block text-sm font-medium text-gray-700">Ingredientes</label>
                            <input type="text" id="ingredients_input"
                                   class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                   placeholder="Digite os ingredientes separados por vírgula">
                            <input type="hidden" name="ingredients" id="ingredients_hidden" value="{{ old('ingredients') ? json_encode(old('ingredients')) : '[]' }}">

                            <div id="ingredients_tags" class="flex flex-wrap gap-2 mt-2"></div>
                            <p class="mt-1 text-xs text-gray-500">Pressione Enter ou vírgula para adicionar ingredientes</p>
                        </div>

                        <div>
                            <label for="allergens_input" class="block text-sm font-medium text-gray-700">Alérgenos</label>
                            <input type="text" id="allergens_input"
                                   class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                   placeholder="Digite os alérgenos separados por vírgula">
                            <input type="hidden" name="allergens" id="allergens_hidden" value="{{ old('allergens') ? json_encode(old('allergens')) : '[]' }}">

                            <div id="allergens_tags" class="flex flex-wrap gap-2 mt-2"></div>
                            <p class="mt-1 text-xs text-gray-500">Ex: Glúten, Lactose, Amendoim, etc.</p>
                        </div>
                    </div>
                </div>

                <!-- Imagem do Item -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Imagem do Item</h2>
                    </div>
                    <div class="px-6 py-6">
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700">Foto do Prato</label>
                            <div class="flex justify-center px-6 pt-5 pb-6 mt-1 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <i class="mx-auto text-gray-400 fas fa-camera fa-3x"></i>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="image" class="relative font-medium text-orange-600 bg-white rounded-md cursor-pointer hover:text-orange-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-orange-500">
                                            <span>Upload de foto</span>
                                            <input id="image" name="image" type="file" accept="image/*" class="sr-only">
                                        </label>
                                        <p class="pl-1">ou arraste e solte</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF até 2MB</p>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                Uma boa foto aumenta significativamente as vendas do item!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Preview do Item -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Preview do Item</h2>
                    </div>
                    <div class="px-6 py-6">
                        <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <p class="mb-3 text-sm font-medium text-gray-700">Como o item aparecerá no menu:</p>
                            <div class="overflow-hidden bg-white border border-gray-300 rounded-lg">
                                <div class="bg-gray-100 aspect-w-16 aspect-h-9">
                                    <div id="preview-image" class="flex items-center justify-center w-full h-48 bg-gray-100">
                                        <i class="text-4xl text-gray-400 fas fa-utensils"></i>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="flex items-start justify-between">
                                        <h3 id="preview-name" class="text-lg font-medium text-gray-900">Nome do Item</h3>
                                        <div id="preview-badges" class="flex items-center space-x-1"></div>
                                    </div>
                                    <p id="preview-description" class="mt-2 text-sm text-gray-600">Descrição do item</p>
                                    <div class="flex items-center justify-between mt-3">
                                        <div class="flex items-center space-x-2">
                                            <span id="preview-price" class="text-lg font-bold text-gray-900">MT 0.00</span>
                                            <span id="preview-discount" class="text-sm text-gray-500 line-through" style="display: none;"></span>
                                        </div>
                                    </div>
                                    <div id="preview-details" class="flex items-center mt-2 space-x-4 text-xs text-gray-500"></div>
                                </div>
                            </div>
                        </div>
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
                        Adicionar ao Menu
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview elements
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const priceInput = document.getElementById('price');
    const discountPriceInput = document.getElementById('discount_price');
    const preparationTimeInput = document.getElementById('preparation_time');
    const caloriesInput = document.getElementById('calories');
    const imageInput = document.getElementById('image');

    // Checkboxes
    const vegetarianInput = document.getElementById('is_vegetarian');
    const veganInput = document.getElementById('is_vegan');
    const spicyInput = document.getElementById('is_spicy');

    // Preview elements
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');
    const previewPrice = document.getElementById('preview-price');
    const previewDiscount = document.getElementById('preview-discount');
    const previewBadges = document.getElementById('preview-badges');
    const previewDetails = document.getElementById('preview-details');
    const previewImage = document.getElementById('preview-image');

    function updatePreview() {
        // Update name and description
        previewName.textContent = nameInput.value || 'Nome do Item';
        previewDescription.textContent = descriptionInput.value || 'Descrição do item';

        // Update price
        const price = parseFloat(priceInput.value) || 0;
        const discountPrice = parseFloat(discountPriceInput.value) || 0;

        if (discountPrice > 0 && discountPrice < price) {
            previewPrice.textContent = `MT ${discountPrice.toFixed(2)}`;
            previewPrice.className = 'text-lg font-bold text-orange-600';
            previewDiscount.textContent = `MT ${price.toFixed(2)}`;
            previewDiscount.style.display = 'block';
        } else {
            previewPrice.textContent = `MT ${price.toFixed(2)}`;
            previewPrice.className = 'text-lg font-bold text-gray-900';
            previewDiscount.style.display = 'none';
        }

        // Update badges
        previewBadges.innerHTML = '';
        if (vegetarianInput.checked) {
            previewBadges.innerHTML += '<span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">🌱</span>';
        }
        if (veganInput.checked) {
            previewBadges.innerHTML += '<span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">🌿</span>';
        }
        if (spicyInput.checked) {
            previewBadges.innerHTML += '<span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">🌶️</span>';
        }

        // Update details
        previewDetails.innerHTML = '';
        const prepTime = parseInt(preparationTimeInput.value);
        const calories = parseInt(caloriesInput.value);

        if (prepTime) {
            previewDetails.innerHTML += `<span><i class="mr-1 fas fa-clock"></i>${prepTime} min</span>`;
        }
        if (calories) {
            previewDetails.innerHTML += `<span><i class="mr-1 fas fa-fire"></i>${calories} cal</span>`;
        }
    }

    // Add event listeners
    [nameInput, descriptionInput, priceInput, discountPriceInput, preparationTimeInput, caloriesInput].forEach(input => {
        input.addEventListener('input', updatePreview);
    });

    [vegetarianInput, veganInput, spicyInput].forEach(input => {
        input.addEventListener('change', updatePreview);
    });

    // Image preview
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.innerHTML = `<img src="${e.target.result}" alt="Preview" class="object-cover w-full h-48">`;
            };
            reader.readAsDataURL(file);
        }
    });

    // Tags functionality
    setupTagsInput('ingredients');
    setupTagsInput('allergens');

    // Initial preview update
    updatePreview();
});

function setupTagsInput(type) {
    const input = document.getElementById(`${type}_input`);
    const hidden = document.getElementById(`${type}_hidden`);
    const container = document.getElementById(`${type}_tags`);

    let tags = [];

    // Load existing tags from old input
    try {
        const existingTags = JSON.parse(hidden.value);
        if (Array.isArray(existingTags)) {
            tags = existingTags;
            updateTagsDisplay();
        }
    } catch (e) {
        // Ignore parsing errors
    }

    function addTag(tagText) {
        const tag = tagText.trim();
        if (tag && !tags.includes(tag)) {
            tags.push(tag);
            updateTagsDisplay();
            updateHiddenInput();
        }
    }

    function removeTag(tagToRemove) {
        tags = tags.filter(tag => tag !== tagToRemove);
        updateTagsDisplay();
        updateHiddenInput();
    }

    function updateTagsDisplay() {
        container.innerHTML = '';
        tags.forEach(tag => {
            const tagElement = document.createElement('span');
            tagElement.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800';
            tagElement.innerHTML = `
                ${tag}
                <button type="button" onclick="this.parentElement.remove(); updateTagsForType('${type}', '${tag}')" class="ml-1.5 inline-flex items-center justify-center w-4 h-4 text-orange-400 hover:text-orange-600">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(tagElement);
        });
    }

    function updateHiddenInput() {
        hidden.value = JSON.stringify(tags);
    }

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addTag(input.value);
            input.value = '';
        }
    });

    input.addEventListener('blur', function() {
        if (input.value.trim()) {
            addTag(input.value);
            input.value = '';
        }
    });

    // Make removeTag function global for this type
    window[`updateTagsForType`] = function(tagType, tagToRemove) {
        if (tagType === type) {
            removeTag(tagToRemove);
        }
    };
}
</script>
@endsection