<nav class="flex flex-col flex-1">
    <ul role="list" class="flex flex-col flex-1 gap-y-7">
        <li>
            <ul role="list" class="-mx-2 space-y-1">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-gray-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                        <i class="fas fa-chart-pie {{ request()->routeIs('admin.dashboard') ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600' }} h-6 w-6 shrink-0"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.restaurants.index') }}" class="{{ request()->routeIs('admin.restaurants.*') ? 'bg-gray-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                        <i class="fas fa-store {{ request()->routeIs('admin.restaurants.*') ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600' }} h-6 w-6 shrink-0"></i>
                        Restaurantes
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'bg-gray-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                        <i class="fas fa-shopping-bag {{ request()->routeIs('admin.orders.*') ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600' }} h-6 w-6 shrink-0"></i>
                        Pedidos
                        @php
                            $pendingOrdersCount = \App\Models\Order::where('status', 'pending')->count();
                        @endphp
                        @if($pendingOrdersCount > 0)
                        <span class="flex items-center justify-center w-6 h-6 ml-auto text-xs text-white bg-red-500 rounded-full">
                            {{ $pendingOrdersCount }}
                        </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'bg-gray-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                        <i class="fas fa-users {{ request()->routeIs('admin.customers.*') ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600' }} h-6 w-6 shrink-0"></i>
                        Clientes
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.delivery-persons.index') }}" class="{{ request()->routeIs('admin.delivery-persons.*') ? 'bg-gray-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                        <i class="fas fa-motorcycle {{ request()->routeIs('admin.delivery-persons.*') ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600' }} h-6 w-6 shrink-0"></i>
                        Entregadores
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.categories.index') }}" class="flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('admin.categories.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }} group">
                        <i class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.categories.*') ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600' }} fas fa-tags"></i>
                        Categorias
                    </a>
                </li>
        <!-- Payments -->
        <li x-data="{ open: {{ request()->routeIs('admin.payments.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="flex w-full items-center gap-x-3 rounded-md p-2 text-left text-sm font-semibold leading-6 {{ request()->routeIs('admin.payments.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }} group">
                <i class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.payments.*') ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600' }} fas fa-credit-card"></i>
                Pagamentos
                <i class="w-5 h-5 ml-auto text-gray-400 shrink-0 fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
            </button>
            <ul x-show="open" x-transition class="px-2 mt-1">
                <li>
                    <a href="{{ route('admin.payments.index') }}" class="flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6 {{ request()->routeIs('admin.payments.index') ? 'bg-orange-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }}">
                        <i class="w-5 h-5 text-gray-400 shrink-0 fas fa-list"></i>
                        Transações
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.payments.methods') }}" class="flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6 {{ request()->routeIs('admin.payments.methods*') ? 'bg-orange-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }}">
                        <i class="w-5 h-5 text-gray-400 shrink-0 fas fa-cog"></i>
                        Métodos
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.payments.analytics') }}" class="flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6 {{ request()->routeIs('admin.payments.analytics') ? 'bg-orange-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }}">
                        <i class="w-5 h-5 text-gray-400 shrink-0 fas fa-chart-pie"></i>
                        Análises
                    </a>
                </li>
            </ul>
        </li>
               <!-- Reports -->
               <li x-data="{ open: {{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="flex w-full items-center gap-x-3 rounded-md p-2 text-left text-sm font-semibold leading-6 {{ request()->routeIs('admin.reports.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }} group">
                    <i class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.reports.*') ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600' }} fas fa-chart-bar"></i>
                    Relatórios
                    <i class="w-5 h-5 ml-auto text-gray-400 shrink-0 fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                </button>
                <ul x-show="open" x-transition class="px-2 mt-1">
                    <li>
                        <a href="{{ route('admin.reports.index') }}" class="flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6 {{ request()->routeIs('admin.reports.index') ? 'bg-orange-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }}">
                            <i class="w-5 h-5 text-gray-400 shrink-0 fas fa-tachometer-alt"></i>
                            Visão Geral
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.sales') }}" class="flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6 {{ request()->routeIs('admin.reports.sales') ? 'bg-orange-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }}">
                            <i class="w-5 h-5 text-gray-400 shrink-0 fas fa-chart-line"></i>
                            Vendas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.restaurants') }}" class="flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6 {{ request()->routeIs('admin.reports.restaurants') ? 'bg-orange-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }}">
                            <i class="w-5 h-5 text-gray-400 shrink-0 fas fa-store"></i>
                            Restaurantes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.deliveries') }}" class="flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6 {{ request()->routeIs('admin.reports.deliveries') ? 'bg-orange-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }}">
                            <i class="w-5 h-5 text-gray-400 shrink-0 fas fa-motorcycle"></i>
                            Entregas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.customers') }}" class="flex gap-x-3 rounded-md py-2 pl-6 pr-2 text-sm leading-6 {{ request()->routeIs('admin.reports.customers') ? 'bg-orange-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }}">
                            <i class="w-5 h-5 text-gray-400 shrink-0 fas fa-users"></i>
                            Clientes
                        </a>
                    </li>
                </ul>
            </li>
                <!-- Settings -->
                <li>
                    <a href="{{ route('admin.settings.index') }}" class="flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('admin.settings.*') ? 'bg-orange-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }} group">
                        <i class="h-6 w-6 shrink-0 {{ request()->routeIs('admin.settings.*') ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600' }} fas fa-cog"></i>
                        Configurações
                    </a>
                </li>
            </ul>
        </li>
        <li class="mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full p-2 -mx-2 text-sm font-semibold leading-6 text-gray-700 rounded-md group gap-x-3 hover:bg-gray-50 hover:text-orange-600">
                    <i class="w-6 h-6 text-gray-400 fas fa-sign-out-alt shrink-0 group-hover:text-orange-600"></i>
                    Sair
                </button>
            </form>
        </li>
    </ul>
</nav>