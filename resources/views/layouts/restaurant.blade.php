<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Restaurante</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="h-full">
    @php
        $restaurant = auth()->user()->restaurants()->first();
    @endphp

    <div x-data="{ sidebarOpen: false }" class="min-h-full">
        <!-- Mobile sidebar overlay -->
        <div x-show="sidebarOpen" class="relative z-50 lg:hidden" role="dialog">
            <div x-show="sidebarOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/80"></div>

            <div class="fixed inset-0 flex">
                <div x-show="sidebarOpen"
                     x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="relative flex flex-1 w-full max-w-xs mr-16">

                    <div class="absolute top-0 flex justify-center w-16 pt-5 left-full">
                        <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                            <span class="sr-only">Fechar sidebar</span>
                            <i class="w-6 h-6 text-white fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Mobile sidebar content -->
                    <div class="flex flex-col px-6 pb-4 overflow-y-auto bg-white grow gap-y-5 ring-1 ring-gray-900/10">
                        <div class="flex items-center h-16">
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-r from-orange-400 to-pink-600">
                                    <i class="text-lg text-white fas fa-store"></i>
                                </div>
                                <div>
                                    <h1 class="text-lg font-bold text-gray-900">{{ $restaurant->name ?? 'Restaurante' }}</h1>
                                    <p class="text-xs text-gray-500">Painel do Restaurante</p>
                                </div>
                            </div>
                        </div>
                        @include('layouts.partials.restaurant-nav')
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
            <div class="flex flex-col px-6 pb-4 overflow-y-auto bg-white border-r border-gray-200 grow gap-y-5">
                <div class="flex items-center h-16">
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-r from-orange-400 to-pink-600">
                            <i class="text-lg text-white fas fa-store"></i>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900">{{ $restaurant->name ?? 'Restaurante' }}</h1>
                            <p class="text-xs text-gray-500">Painel do Restaurante</p>
                        </div>
                    </div>
                </div>
                @include('layouts.partials.restaurant-nav')
            </div>
        </div>

        <!-- Main content -->
        <div class="lg:pl-72">
            <!-- Top navigation -->
            <div class="sticky top-0 z-40 flex items-center h-16 px-4 bg-white border-b border-gray-200 shadow-sm shrink-0 gap-x-4 sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Abrir sidebar</span>
                    <i class="w-5 h-5 fas fa-bars"></i>
                </button>

                <!-- Restaurant Status -->
                @if($restaurant)
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="h-3 w-3 rounded-full {{ $restaurant->isOpen() ? 'bg-green-500' : 'bg-red-500' }}"></div>
                        <span class="text-sm font-medium {{ $restaurant->isOpen() ? 'text-green-700' : 'text-red-700' }}">
                            {{ $restaurant->isOpen() ? 'Aberto' : 'Fechado' }}
                        </span>
                    </div>
                </div>
                @endif

                <div class="flex self-stretch flex-1 gap-x-4 lg:gap-x-6">
                    <div class="flex flex-1"></div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- Notifications -->
                        <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500 relative">
                            <span class="sr-only">Ver notificações</span>
                            <i class="w-6 h-6 fas fa-bell"></i>
                            @if($restaurant)
                                @php
                                    $pendingOrders = \App\Models\Order::where('restaurant_id', $restaurant->id)->where('status', 'pending')->count();
                                @endphp
                                @if($pendingOrders > 0)
                                <span class="absolute flex items-center justify-center w-4 h-4 text-xs text-white bg-red-500 rounded-full -top-1 -right-1">
                                    {{ $pendingOrders }}
                                </span>
                                @endif
                            @endif
                        </button>

                        <!-- Profile dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button type="button" class="-m-1.5 flex items-center p-1.5" @click="open = !open">
                                <span class="sr-only">Abrir menu do usuário</span>
                                <img class="w-8 h-8 rounded-full bg-gray-50" src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=f97316&color=fff' }}" alt="">
                                <span class="hidden lg:flex lg:items-center">
                                    <span class="ml-4 text-sm font-semibold leading-6 text-gray-900">{{ auth()->user()->name }}</span>
                                    <i class="w-5 h-5 ml-2 text-gray-400 fas fa-chevron-down"></i>
                                </span>
                            </button>

                            <div x-show="open" @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 z-10 mt-2.5 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5">
                                <a href="{{ route('profile.edit') }}" class="block px-3 py-1 text-sm leading-6 text-gray-900 hover:bg-gray-50">Seu perfil</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-3 py-1 text-sm leading-6 text-left text-gray-900 hover:bg-gray-50">Sair</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @yield('content')
        </div>
    </div>
</body>
</html>