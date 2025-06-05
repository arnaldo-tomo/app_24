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
                            <a href="{{ route('admin.settings.index') }}" class="text-gray-400 hover:text-gray-500">
                                <i class="flex-shrink-0 w-5 h-5 fas fa-cog"></i>
                                <span class="sr-only">Configurações</span>
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="flex-shrink-0 w-5 h-5 text-gray-300 fas fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium text-gray-500">Nova Configuração</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold leading-6 text-gray-900">Criar Nova Configuração</h1>
                <p class="mt-2 text-sm text-gray-700">Adicione uma nova configuração ao sistema</p>
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

            <form action="{{ route('admin.settings.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- Informações Básicas -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Informações da Configuração</h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="key" class="block text-sm font-medium text-gray-700">Chave *</label>
                                <input type="text" name="key" id="key" value="{{ old('key') }}" required
                                       class="block w-full mt-1 font-mono border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="ex: app_name, delivery_fee">
                                <p class="mt-1 text-xs text-gray-500">Use snake_case para a chave (ex: app_name, delivery_fee)</p>
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Tipo *</label>
                                <select name="type" id="type" required onchange="toggleValueField()"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Selecione um tipo</option>
                                    @foreach(\App\Models\Setting::TYPES as $value => $label)
                                        <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="group" class="block text-sm font-medium text-gray-700">Grupo *</label>
                                <select name="group" id="group" required
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Selecione um grupo</option>
                                    @foreach(\App\Models\Setting::GROUPS as $value => $label)
                                        <option value="{{ $value }}" {{ old('group') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_public" id="is_public" value="1"
                                       {{ old('is_public') ? 'checked' : '' }}
                                       class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                <label for="is_public" class="ml-2 text-sm font-medium text-gray-700">
                                    Configuração Pública
                                </label>
                                <div class="ml-2">
                                    <i class="text-gray-400 fas fa-info-circle" title="Configurações públicas são acessíveis no frontend"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" id="description" rows="2"
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                      placeholder="Descrição opcional da configuração">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Valor da Configuração -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Valor da Configuração</h2>
                    </div>
                    <div class="px-6 py-6">
                        <!-- String Input -->
                        <div id="string-input" class="value-input">
                            <label for="value_string" class="block text-sm font-medium text-gray-700">Valor (Texto)</label>
                            <input type="text" name="value" id="value_string" value="{{ old('value') }}"
                                   class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                   placeholder="Digite o valor">
                        </div>

                        <!-- Number Input -->
                        <div id="number-input" class="value-input" style="display: none;">
                            <label for="value_number" class="block text-sm font-medium text-gray-700">Valor (Número)</label>
                            <input type="number" name="value" id="value_number" value="{{ old('value') }}" step="0.01"
                                   class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                   placeholder="Digite o valor numérico">
                        </div>

                        <!-- Boolean Input -->
                        <div id="boolean-input" class="value-input" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700">Valor (Sim/Não)</label>
                            <div class="flex items-center mt-2 space-x-6">
                                <div class="flex items-center">
                                    <input type="radio" name="value" id="value_true" value="1"
                                           {{ old('value') == '1' ? 'checked' : '' }}
                                           class="w-4 h-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                                    <label for="value_true" class="ml-2 text-sm text-gray-700">Sim</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="value" id="value_false" value="0"
                                           {{ old('value') == '0' ? 'checked' : '' }}
                                           class="w-4 h-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                                    <label for="value_false" class="ml-2 text-sm text-gray-700">Não</label>
                                </div>
                            </div>
                        </div>

                        <!-- JSON Input -->
                        <div id="json-input" class="value-input" style="display: none;">
                            <label for="value_json" class="block text-sm font-medium text-gray-700">Valor (JSON)</label>
                            <textarea name="value" id="value_json" rows="4"
                                      class="block w-full mt-1 font-mono border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                      placeholder='{"chave": "valor"}'>{{ old('value') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Digite um JSON válido</p>
                        </div>

                        <!-- File Input -->
                        <div id="file-input" class="value-input" style="display: none;">
                            <label for="value_file" class="block text-sm font-medium text-gray-700">Arquivo</label>
                            <div class="flex justify-center px-6 pt-5 pb-6 mt-1 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <i class="mx-auto text-gray-400 fas fa-cloud-upload-alt fa-3x"></i>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file" class="relative font-medium text-orange-600 bg-white rounded-md cursor-pointer hover:text-orange-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-orange-500">
                                            <span>Upload de arquivo</span>
                                            <input id="file" name="file" type="file" class="sr-only">
                                        </label>
                                        <p class="pl-1">ou arraste e solte</p>
                                    </div>
                                    <p class="text-xs text-gray-500">Até 2MB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exemplos por Tipo -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Exemplos de Configurações</h2>
                    </div>
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
                                <h4 class="text-sm font-medium text-blue-800">Texto</h4>
                                <p class="mt-1 text-xs text-blue-700">app_name: "FoodDelivery"</p>
                                <p class="mt-1 text-xs text-blue-700">contact_email: "contato@app.com"</p>
                            </div>

                            <div class="p-4 border border-green-200 rounded-lg bg-green-50">
                                <h4 class="text-sm font-medium text-green-800">Número</h4>
                                <p class="mt-1 text-xs text-green-700">delivery_fee: 5.00</p>
                                <p class="mt-1 text-xs text-green-700">commission_rate: 15.5</p>
                            </div>

                            <div class="p-4 border border-purple-200 rounded-lg bg-purple-50">
                                <h4 class="text-sm font-medium text-purple-800">Sim/Não</h4>
                                <p class="mt-1 text-xs text-purple-700">maintenance_mode: Não</p>
                                <p class="mt-1 text-xs text-purple-700">notifications_enabled: Sim</p>
                            </div>

                            <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50">
                                <h4 class="text-sm font-medium text-yellow-800">JSON</h4>
                                <p class="mt-1 text-xs text-yellow-700">social_links: {"facebook": "url"}</p>
                                <p class="mt-1 text-xs text-yellow-700">api_config: {"timeout": 30}</p>
                            </div>

                            <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <h4 class="text-sm font-medium text-gray-800">Arquivo</h4>
                                <p class="mt-1 text-xs text-gray-700">app_logo: logo.png</p>
                                <p class="mt-1 text-xs text-gray-700">favicon: favicon.ico</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.settings.index') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <i class="mr-2 fas fa-save"></i>
                        Criar Configuração
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
function toggleValueField() {
    const type = document.getElementById('type').value;
    const valueInputs = document.querySelectorAll('.value-input');

    // Hide all value inputs
    valueInputs.forEach(input => {
        input.style.display = 'none';
        // Clear the name attribute to prevent multiple values being sent
        const nameInputs = input.querySelectorAll('[name="value"]');
        nameInputs.forEach(nameInput => {
            nameInput.removeAttribute('name');
        });
    });

    // Show the appropriate input based on type
    let targetInput;
    switch(type) {
        case 'string':
            targetInput = document.getElementById('string-input');
            break;
        case 'number':
            targetInput = document.getElementById('number-input');
            break;
        case 'boolean':
            targetInput = document.getElementById('boolean-input');
            break;
        case 'json':
            targetInput = document.getElementById('json-input');
            break;
        case 'file':
            targetInput = document.getElementById('file-input');
            break;
        default:
            targetInput = document.getElementById('string-input');
    }

    if (targetInput) {
        targetInput.style.display = 'block';
        // Restore name attribute to the active input
        const activeInputs = targetInput.querySelectorAll('input[type="text"], input[type="number"], input[type="radio"], textarea');
        activeInputs.forEach(input => {
            if (input.type === 'radio') {
                input.setAttribute('name', 'value');
            } else {
                input.setAttribute('name', 'value');
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize with default type
    toggleValueField();

    // Auto-generate key from description or other field
    const keyInput = document.getElementById('key');
    keyInput.addEventListener('input', function() {
        this.value = this.value.toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
    });

    // JSON validation
    const jsonInput = document.getElementById('value_json');
    jsonInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            try {
                JSON.parse(this.value);
                this.classList.remove('border-red-300');
                this.classList.add('border-gray-300');
            } catch (e) {
                this.classList.remove('border-gray-300');
                this.classList.add('border-red-300');
                alert('JSON inválido. Verifique a sintaxe.');
            }
        }
    });
});
</script>
@endsection