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
                            <a href="{{ route('admin.restaurants.index') }}" class="text-gray-400 hover:text-gray-500">
                                <i class="flex-shrink-0 w-5 h-5 fas fa-store"></i>
                                <span class="sr-only">Restaurantes</span>
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="flex-shrink-0 w-5 h-5 text-gray-300 fas fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium text-gray-500">Novo Restaurante</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold leading-6 text-gray-900">Criar Novo Restaurante</h1>
                <p class="mt-2 text-sm text-gray-700">Preencha as informações para cadastrar um novo restaurante</p>
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

            <form action="{{ route('admin.restaurants.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- Informações Básicas -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Informações Básicas</h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nome do Restaurante *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700">Proprietário *</label>
                                <select name="user_id" id="user_id" required
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Selecione um proprietário</option>
                                    @foreach($owners as $owner)
                                        <option value="{{ $owner->id }}" {{ old('user_id') == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }} ({{ $owner->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" id="description" rows="3"
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">{{ old('description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Telefone *</label>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">E-mail *</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="categories" class="block text-sm font-medium text-gray-700">Categorias</label>
                            <div class="grid grid-cols-2 gap-4 mt-2 md:grid-cols-3 lg:grid-cols-4">
                                @foreach($categories as $category)
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                               {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}
                                               class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                        <span class="ml-2 text-sm text-gray-700">{{ $category->icon ?? '🍽️' }} {{ $category->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Localização -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Localização</h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Endereço *</label>
                            <textarea name="address" id="address" rows="2" required
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">{{ old('address') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                                <input type="number" name="latitude" id="latitude" value="{{ old('latitude') }}" step="any"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                                <input type="number" name="longitude" id="longitude" value="{{ old('longitude') }}" step="any"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configurações de Delivery -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Configurações de Delivery</h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label for="delivery_fee" class="block text-sm font-medium text-gray-700">Taxa de Entrega *</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">MT</span>
                                    </div>
                                    <input type="number" name="delivery_fee" id="delivery_fee" value="{{ old('delivery_fee') }}"
                                           min="0" step="0.01" required
                                           class="block w-full pl-12 border-gray-300 rounded-md focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                            </div>

                            <div>
                                <label for="minimum_order" class="block text-sm font-medium text-gray-700">Pedido Mínimo *</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">MT</span>
                                    </div>
                                    <input type="number" name="minimum_order" id="minimum_order" value="{{ old('minimum_order') }}"
                                           min="0" step="0.01" required
                                           class="block w-full pl-12 border-gray-300 rounded-md focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="delivery_time_min" class="block text-sm font-medium text-gray-700">Tempo Mínimo de Entrega (min) *</label>
                                <input type="number" name="delivery_time_min" id="delivery_time_min" value="{{ old('delivery_time_min') }}"
                                       min="1" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="delivery_time_max" class="block text-sm font-medium text-gray-700">Tempo Máximo de Entrega (min) *</label>
                                <input type="number" name="delivery_time_max" id="delivery_time_max" value="{{ old('delivery_time_max') }}"
                                       min="1" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Horário de Funcionamento -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Horário de Funcionamento</h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="opening_time" class="block text-sm font-medium text-gray-700">Horário de Abertura *</label>
                                <input type="time" name="opening_time" id="opening_time" value="{{ old('opening_time') }}" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="closing_time" class="block text-sm font-medium text-gray-700">Horário de Fechamento *</label>
                                <input type="time" name="closing_time" id="closing_time" value="{{ old('closing_time') }}" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dias de Funcionamento *</label>
                            <div class="grid grid-cols-2 gap-4 mt-2 md:grid-cols-4 lg:grid-cols-7">
                                @php
                                    $days = [
                                        'monday' => 'Segunda',
                                        'tuesday' => 'Terça',
                                        'wednesday' => 'Quarta',
                                        'thursday' => 'Quinta',
                                        'friday' => 'Sexta',
                                        'saturday' => 'Sábado',
                                        'sunday' => 'Domingo'
                                    ];
                                @endphp
                                @foreach($days as $value => $label)
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="checkbox" name="working_days[]" value="{{ $value }}"
                                               {{ in_array($value, old('working_days', [])) ? 'checked' : '' }}
                                               class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                        <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Imagens -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Imagens</h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="image" class="block text-sm font-medium text-gray-700">Logo do Restaurante</label>
                                <div class="flex justify-center px-6 pt-5 pb-6 mt-1 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <i class="mx-auto text-gray-400 fas fa-image fa-3x"></i>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="image" class="relative font-medium text-orange-600 bg-white rounded-md cursor-pointer hover:text-orange-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-orange-500">
                                                <span>Upload de arquivo</span>
                                                <input id="image" name="image" type="file" accept="image/*" class="sr-only">
                                            </label>
                                            <p class="pl-1">ou arraste e solte</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF até 2MB</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="cover_image" class="block text-sm font-medium text-gray-700">Imagem de Capa</label>
                                <div class="flex justify-center px-6 pt-5 pb-6 mt-1 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <i class="mx-auto text-gray-400 fas fa-image fa-3x"></i>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="cover_image" class="relative font-medium text-orange-600 bg-white rounded-md cursor-pointer hover:text-orange-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-orange-500">
                                                <span>Upload de arquivo</span>
                                                <input id="cover_image" name="cover_image" type="file" accept="image/*" class="sr-only">
                                            </label>
                                            <p class="pl-1">ou arraste e solte</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF até 2MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.restaurants.index') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <i class="mr-2 fas fa-save"></i>
                        Criar Restaurante
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection