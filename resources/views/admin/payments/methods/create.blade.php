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
                            <a href="{{ route('admin.payments.methods') }}" class="text-gray-400 hover:text-gray-500">
                                <i class="flex-shrink-0 w-5 h-5 fas fa-credit-card"></i>
                                <span class="sr-only">Métodos de Pagamento</span>
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="flex-shrink-0 w-5 h-5 text-gray-300 fas fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium text-gray-500">Novo Método</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold leading-6 text-gray-900">Criar Método de Pagamento</h1>
                <p class="mt-2 text-sm text-gray-700">Adicione um novo método de pagamento ao sistema</p>
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

            <form action="{{ route('admin.payments.methods.store') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Informações Básicas -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Informações Básicas</h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nome do Método *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="Ex: PIX, Cartão de Crédito">
                            </div>

                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Tipo de Pagamento *</label>
                                <select name="type" id="type" required
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Selecione um tipo</option>
                                    @foreach(\App\Models\PaymentMethod::TYPES as $value => $label)
                                        <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea name="description" id="description" rows="3"
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                      placeholder="Descrição opcional do método de pagamento">{{ old('description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700">Ícone (Font Awesome)</label>
                                <div class="relative mt-1">
                                    <input type="text" name="icon" id="icon" value="{{ old('icon') }}"
                                           class="block w-full pr-12 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                           placeholder="fas fa-credit-card">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i id="icon-preview" class="text-gray-400 fas fa-credit-card"></i>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Use classes do Font Awesome (ex: fas fa-credit-card)</p>
                            </div>

                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700">Ordem de Exibição</label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="0">
                                <p class="mt-1 text-xs text-gray-500">Menor número aparece primeiro</p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked
                                   class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                            <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
                                Método Ativo
                            </label>
                            <p class="ml-2 text-xs text-gray-500">Métodos ativos ficam disponíveis para os clientes</p>
                        </div>
                    </div>
                </div>

                <!-- Configurações de Taxa -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="fee_percentage" class="block text-sm font-medium text-gray-700">Taxa Percentual (%)</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <input type="number" name="fee_percentage" id="fee_percentage" value="{{ old('fee_percentage', 0) }}"
                                           min="0" max="100" step="0.01"
                                           class="block w-full pr-12 border-gray-300 rounded-md focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                           placeholder="0.00">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">%</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Taxa sobre o valor da transação (ex: 3.99)</p>
                            </div>

                            <div>
                                <label for="fee_fixed" class="block text-sm font-medium text-gray-700">Taxa Fixa (MT)</label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">MT</span>
                                    </div>
                                    <input type="number" name="fee_fixed" id="fee_fixed" value="{{ old('fee_fixed', 0) }}"
                                           min="0" step="0.01"
                                           class="block w-full pl-12 border-gray-300 rounded-md focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                           placeholder="0.00">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Taxa fixa por transação (ex: 0.50)</p>
                            </div>
                        </div>

                        <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="text-blue-400 fas fa-info-circle"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Como funcionam as taxas:</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ul class="pl-5 space-y-1 list-disc">
                                            <li><strong>Taxa Percentual:</strong> Aplicada sobre o valor total da transação</li>
                                            <li><strong>Taxa Fixa:</strong> Valor fixo cobrado independente do valor da transação</li>
                                            <li><strong>Exemplo:</strong> Para uma compra de MT 100 com taxa de 3% + MT 0.50 = MT 3.50 total de taxa</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calculadora de Taxa -->
                        <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <h4 class="mb-3 text-sm font-medium text-gray-700">Simulador de Taxa</h4>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Valor da Compra (MT)</label>
                                    <input type="number" id="calc_amount" value="100" step="0.01" min="0"
                                           class="block w-full mt-1 text-sm border-gray-300 rounded-md focus:border-orange-500 focus:ring-orange-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Taxa Total</label>
                                    <div id="calc_fee" class="block w-full px-3 py-2 mt-1 text-sm bg-white border border-gray-300 rounded-md">
                                        MT 0.00
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Valor Líquido</label>
                                    <div id="calc_net" class="block w-full px-3 py-2 mt-1 text-sm bg-white border border-gray-300 rounded-md">
                                        MT 100.00
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview do Método -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Preview</h2>
                    </div>
                    <div class="px-6 py-6">
                        <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <p class="mb-3 text-sm font-medium text-gray-700">Como o método aparecerá para os clientes:</p>
                            <div class="inline-flex items-center px-4 py-3 bg-white border border-gray-300 rounded-lg shadow-sm">
                                <div class="flex items-center justify-center w-10 h-10 mr-3 bg-orange-100 rounded-lg">
                                    <i id="preview-icon" class="text-orange-600 fas fa-credit-card"></i>
                                </div>
                                <div>
                                    <div id="preview-name" class="text-sm font-medium text-gray-900">Nome do Método</div>
                                    <div id="preview-description" class="text-xs text-gray-500">Descrição do método</div>
                                </div>
                                <div class="ml-auto">
                                    <span id="preview-status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                        Ativo
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.payments.methods') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                        <i class="mr-2 fas fa-save"></i>
                        Criar Método
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
    const iconInput = document.getElementById('icon');
    const isActiveInput = document.getElementById('is_active');
    const feePercentageInput = document.getElementById('fee_percentage');
    const feeFixedInput = document.getElementById('fee_fixed');
    const calcAmountInput = document.getElementById('calc_amount');

    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');
    const previewIcon = document.getElementById('preview-icon');
    const previewStatus = document.getElementById('preview-status');
    const iconPreview = document.getElementById('icon-preview');
    const calcFee = document.getElementById('calc_fee');
    const calcNet = document.getElementById('calc_net');

    function updatePreview() {
        // Update name
        const name = nameInput.value || 'Nome do Método';
        previewName.textContent = name;

        // Update description
        const description = descriptionInput.value || 'Descrição do método';
        previewDescription.textContent = description;

        // Update icon
        const icon = iconInput.value || 'fas fa-credit-card';
        previewIcon.className = `text-orange-600 ${icon}`;
        iconPreview.className = `text-gray-400 ${icon}`;

        // Update status
        const isActive = isActiveInput.checked;
        if (isActive) {
            previewStatus.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
            previewStatus.innerHTML = '<span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>Ativo';
        } else {
            previewStatus.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
            previewStatus.innerHTML = '<span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>Inativo';
        }

        // Update fee calculator
        updateFeeCalculator();
    }

    function updateFeeCalculator() {
        const amount = parseFloat(calcAmountInput.value) || 0;
        const feePercentage = parseFloat(feePercentageInput.value) || 0;
        const feeFixed = parseFloat(feeFixedInput.value) || 0;

        const percentageFee = (amount * feePercentage) / 100;
        const totalFee = percentageFee + feeFixed;
        const netAmount = amount - totalFee;

        calcFee.textContent = `MT ${totalFee.toFixed(2)}`;
        calcNet.textContent = `MT ${netAmount.toFixed(2)}`;
    }

    // Add event listeners
    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    iconInput.addEventListener('input', updatePreview);
    isActiveInput.addEventListener('change', updatePreview);
    feePercentageInput.addEventListener('input', updateFeeCalculator);
    feeFixedInput.addEventListener('input', updateFeeCalculator);
    calcAmountInput.addEventListener('input', updateFeeCalculator);

    // Initial preview update
    updatePreview();

    // Icon suggestions based on type
    const typeInput = document.getElementById('type');
    const iconSuggestions = {
        'card': 'fas fa-credit-card',
        'pix': 'fas fa-qrcode',
        'cash': 'fas fa-money-bill-wave',
        'bank_transfer': 'fas fa-university',
        'digital_wallet': 'fas fa-mobile-alt'
    };

    typeInput.addEventListener('change', function() {
        const selectedType = this.value;
        if (iconSuggestions[selectedType] && !iconInput.value) {
            iconInput.value = iconSuggestions[selectedType];
            updatePreview();
        }
    });
});
</script>
@endsection