@extends('layouts.admin')

@section('content')
<div class="min-h-full">
    <!-- Page content -->
    <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-bold leading-6 text-gray-900">Configurações do Sistema</h1>
                    <p class="mt-2 text-sm text-gray-700">Gerencie as configurações globais da plataforma</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <a href="{{ route('admin.settings.create') }}" class="block px-3 py-2 text-sm font-semibold text-center text-white bg-orange-600 rounded-md shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600">
                        <i class="mr-2 fas fa-plus"></i>Nova Configuração
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

            <!-- Filters -->
            <div class="mt-8 bg-white rounded-lg shadow">
                <div class="px-4 py-5 sm:p-6">
                    <form method="GET" action="{{ route('admin.settings.index') }}">
                        <div class="flex flex-wrap gap-4">
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700">Buscar</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                       placeholder="Chave ou descrição">
                            </div>
                            <div class="flex-1 min-w-0">
                                <label class="block text-sm font-medium text-gray-700">Grupo</label>
                                <select name="group" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    <option value="">Todos os grupos</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group }}" {{ request('group') == $group ? 'selected' : '' }}>
                                            {{ \App\Models\Setting::GROUPS[$group] ?? ucfirst($group) }}
                                        </option>
                                    @endforeach
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

            <!-- Settings by Group -->
            <div class="mt-8 space-y-8">
                @php
                    $groupedSettings = $settings->groupBy('group');
                    $groupIcons = [
                        'general' => 'fa-globe',
                        'delivery' => 'fa-motorcycle',
                        'payment' => 'fa-credit-card',
                        'notification' => 'fa-bell',
                        'appearance' => 'fa-palette'
                    ];
                @endphp

                @forelse($groupedSettings as $group => $groupSettings)
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">
                            <i class="mr-2 text-orange-500 fas {{ $groupIcons[$group] ?? 'fa-cog' }}"></i>
                            {{ \App\Models\Setting::GROUPS[$group] ?? ucfirst($group) }}
                        </h2>
                    </div>

                    <form action="{{ route('admin.settings.bulk-update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="p-6 space-y-6">
                            @foreach($groupSettings as $setting)
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700">
                                        {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        @if($setting->is_public)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">
                                                <i class="mr-1 fas fa-eye"></i>Público
                                            </span>
                                        @endif
                                    </label>
                                    @if($setting->description)
                                        <p class="mt-1 text-xs text-gray-500">{{ $setting->description }}</p>
                                    @endif
                                </div>

                                <div class="md:col-span-1">
                                    @if($setting->type === 'boolean')
                                        <div class="flex items-center">
                                            <input type="checkbox" name="settings[{{ $setting->key }}]"
                                                   id="setting_{{ $setting->id }}"
                                                   {{ $setting->getCastedValue() ? 'checked' : '' }}
                                                   class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                            <label for="setting_{{ $setting->id }}" class="ml-2 text-sm text-gray-700">
                                                {{ $setting->getCastedValue() ? 'Ativado' : 'Desativado' }}
                                            </label>
                                        </div>
                                    @elseif($setting->type === 'file')
                                        <div class="space-y-2">
                                            @if($setting->value)
                                                <div class="flex items-center space-x-2">
                                                    <img src="{{ asset('storage/' . $setting->value) }}"
                                                         alt="Current file"
                                                         class="object-cover w-16 h-16 rounded">
                                                    <div class="text-sm text-gray-500">
                                                        <p>Arquivo atual</p>
                                                        <p class="text-xs">{{ basename($setting->value) }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                            <input type="file" name="settings[{{ $setting->key }}]"
                                                   accept="image/*"
                                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                                        </div>
                                    @elseif($setting->type === 'number')
                                        <input type="number" name="settings[{{ $setting->key }}]"
                                               value="{{ $setting->value }}" step="0.01"
                                               class="block w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    @else
                                        <input type="text" name="settings[{{ $setting->key }}]"
                                               value="{{ $setting->value }}"
                                               class="block w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                                    @endif
                                </div>

                                <div class="flex items-center space-x-2 md:col-span-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $setting->getTypeLabel() }}
                                    </span>
                                    <a href="{{ route('admin.settings.edit', $setting) }}"
                                       class="text-orange-600 hover:text-orange-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.settings.destroy', $setting) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Tem certeza que deseja excluir esta configuração?')"
                                                class="text-red-600 hover:text-red-900" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                <i class="mr-2 fas fa-save"></i>
                                Salvar {{ \App\Models\Setting::GROUPS[$group] ?? ucfirst($group) }}
                            </button>
                        </div>
                    </form>
                </div>
                @empty
                <div class="py-12 text-center">
                    <div class="flex flex-col items-center">
                        <i class="mb-4 text-6xl text-gray-300 fas fa-cog"></i>
                        <h3 class="mb-2 text-lg font-medium text-gray-900">Nenhuma configuração encontrada</h3>
                        <p class="mb-4 text-gray-500">Comece criando a primeira configuração do sistema.</p>
                        <a href="{{ route('admin.settings.create') }}"
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700">
                            <i class="mr-2 fas fa-plus"></i>
                            Criar Configuração
                        </a>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($settings->hasPages())
            <div class="mt-6">
                {{ $settings->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </main>
</div>

@php
function getGroupIcon($group) {
    return match($group) {
        'general' => 'fa-globe',
        'delivery' => 'fa-motorcycle',
        'payment' => 'fa-credit-card',
        'notification' => 'fa-bell',
        'appearance' => 'fa-palette',
        default => 'fa-cog'
    };
}
@endphp
@endsection