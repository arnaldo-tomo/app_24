<!DOCTYPE html>
<html lang="pt" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
    <div class="flex min-h-full">
        <!-- Left side - Login Form -->
        <div class="flex flex-col justify-center flex-1 px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="w-full max-w-sm mx-auto lg:w-96">
                <div>
                    <!-- Logo/Brand -->
                    <div class="flex items-center justify-center mb-8">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #fb923c, #ec4899);">
                              <i class="fas fa-utensils" style="font-size: 18px; color: white;"></i>
                            </div>
                            <h1 style="font-size: 20px; font-weight: bold; color: #111827; margin: 0;">Meu24</h1>
                          </div>
                    </div>

                    <h2 class="text-3xl font-bold tracking-tight text-gray-900">Entrar na sua conta</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Ou
                        <a href="{{ route('register') }}" class="font-medium text-orange-600 hover:text-orange-500">
                            crie uma nova conta
                        </a>
                    </p>
                </div>

                <div class="mt-8">
                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="p-4 mb-4 rounded-md bg-green-50">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="text-green-400 fas fa-check-circle"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="p-4 mb-4 rounded-md bg-red-50">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="text-red-400 fas fa-exclamation-circle"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        {{ __('Whoops! Algo deu errado.') }}
                                    </h3>
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

                    <form class="space-y-6" action="{{ route('login') }}" method="POST">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                E-mail
                            </label>
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="text-gray-400 fas fa-envelope"></i>
                                </div>
                                <input id="email" name="email" type="email" autocomplete="email" required
                                       value="{{ old('email') }}"
                                       class="block w-full py-2 pl-10 pr-3 border border-gray-300 rounded-md shadow-sm appearance-none placeholder-gray-400 focus:border-orange-500 focus:outline-none focus:ring-orange-500 sm:text-sm @error('email') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                       placeholder="Digite seu e-mail">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Senha
                            </label>
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="text-gray-400 fas fa-lock"></i>
                                </div>
                                <input id="password" name="password" type="password" autocomplete="current-password" required
                                       class="block w-full py-2 pl-10 pr-3 border border-gray-300 rounded-md shadow-sm appearance-none placeholder-gray-400 focus:border-orange-500 focus:outline-none focus:ring-orange-500 sm:text-sm @error('password') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                       placeholder="Digite sua senha">
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember-me" name="remember" type="checkbox"
                                       class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                <label for="remember-me" class="block ml-2 text-sm text-gray-900">
                                    Lembrar de mim
                                </label>
                            </div>

                            <div class="text-sm">
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="font-medium text-orange-600 hover:text-orange-500">
                                        Esqueceu sua senha?
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div>
                            <button type="submit"
                                    class="flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="mr-2 fas fa-sign-in-alt"></i>
                                Entrar
                            </button>
                        </div>

                        <!-- Divider -->
                        <div class="mt-6">
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-300"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-2 text-gray-500 bg-gray-50">Ou continue com</span>
                                </div>
                            </div>

                            <!-- Social Login Buttons (Optional) -->
                            <div class="grid grid-cols-2 gap-3 mt-6">
                                <button type="button"
                                        class="inline-flex justify-center w-full px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                                    <i class="text-blue-600 fab fa-google"></i>
                                    <span class="ml-2">Google</span>
                                </button>

                                <button type="button"
                                        class="inline-flex justify-center w-full px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                                    <i class="text-blue-800 fab fa-facebook-f"></i>
                                    <span class="ml-2">Facebook</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Demo Accounts (Optional - for development) -->
                    @if (app()->environment('local'))
                    <div class="p-4 mt-8 rounded-lg bg-blue-50">
                        <h4 class="mb-2 text-sm font-medium text-blue-800">Contas Demo:</h4>
                        <div class="space-y-1 text-xs text-blue-700">
                            <div><strong>Admin:</strong> admin@example.com | password</div>
                            <div><strong>Restaurante:</strong> restaurant@example.com | password</div>
                            <div><strong>Entregador:</strong> delivery@example.com | password</div>
                            <div><strong>Cliente:</strong> customer@example.com | password</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right side - Image/Illustration -->
        <div class="relative flex-1 hidden w-0 lg:block">
            <div class="absolute inset-0 bg-gradient-to-br from-orange-400 to-red-600">
                <div class="absolute inset-0 bg-black opacity-20"></div>
                <div class="relative flex flex-col items-center justify-center h-full p-12 text-center">
                    <div class="max-w-lg">
                        <i class="mb-8 text-6xl text-white fas fa-motorcycle opacity-90"></i>
                        <h2 class="mb-4 text-4xl font-bold text-white">
                            Bem-vindo de volta!
                        </h2>
                        <p class="text-xl text-white opacity-90">
                            Gerencie seu sistema de delivery de forma simples e eficiente.
                            Controle restaurantes, entregadores e pedidos em um só lugar.
                        </p>
                        <div class="flex justify-center mt-8 space-x-6">
                            <div class="text-center">
                                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-2 bg-white rounded-full bg-opacity-20">
                                    <i class="text-2xl text-white fas fa-store"></i>
                                </div>
                                <p class="text-sm text-white">Restaurantes</p>
                            </div>
                            <div class="text-center">
                                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-2 bg-white rounded-full bg-opacity-20">
                                    <i class="text-2xl text-white fas fa-motorcycle"></i>
                                </div>
                                <p class="text-sm text-white">Entregas</p>
                            </div>
                            <div class="text-center">
                                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-2 bg-white rounded-full bg-opacity-20">
                                    <i class="text-2xl text-white fas fa-chart-line"></i>
                                </div>
                                <p class="text-sm text-white">Análises</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State Script (Optional) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitButton = form.querySelector('button[type="submit"]');

            form.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="mr-2 fas fa-spinner fa-spin"></i>Entrando...';
            });
        });
    </script>
</body>
</html>