<nav class="flex flex-col flex-1">
    <ul role="list" class="flex flex-col flex-1 gap-y-7">
        <li>
            <ul role="list" class="-mx-2 space-y-1">
                <li>
                    <a href="{{ route('restaurant.dashboard') }}" class="{{ request()->routeIs('restaurant.dashboard') ? 'bg-gray-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                        <i class="fas fa-chart-pie {{ request()->routeIs('restaurant.dashboard') ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600' }} h-6 w-6 shrink-0"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('restaurant.orders.index') }}" class="{{ request()->routeIs('restaurant.orders.*') ? 'bg-gray-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                        <i class="fas fa-shopping-bag {{ request()->routeIs('restaurant.orders.*') ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600' }} h-6 w-6 shrink-0"></i>
                        Pedidos
                        @php
                            $restaurant = auth()->user()->restaurants()->first();
                            $pendingCount = $restaurant ? \App\Models\Order::where('restaurant_id', $restaurant->id)->where('status', 'pending')->count() : 0;
                        @endphp
                        @if($pendingCount > 0)
                        <span class="flex items-center justify-center w-6 h-6 ml-auto text-xs text-white bg-red-500 rounded-full">
                            {{ $pendingCount }}
                        </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('restaurant.menu.index') }}" class="{{ request()->routeIs('restaurant.menu.*') ? 'bg-gray-50 text-orange-600' : 'text-gray-700 hover:text-orange-600 hover:bg-gray-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                        <i class="fas fa-utensils {{ request()->routeIs('restaurant.menu.*') ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600' }} h-6 w-6 shrink-0"></i>
                        Menu
                    </a>
                </li>
                <li>
                    <a href="#" class="flex p-2 text-sm font-semibold leading-6 text-gray-700 rounded-md hover:text-orange-600 hover:bg-gray-50 group gap-x-3">
                        <i class="w-6 h-6 text-gray-400 fas fa-chart-bar group-hover:text-orange-600 shrink-0"></i>
                        Relatórios
                    </a>
                </li>
                <li>
                    <a href="#" class="flex p-2 text-sm font-semibold leading-6 text-gray-700 rounded-md hover:text-orange-600 hover:bg-gray-50 group gap-x-3">
                        <i class="w-6 h-6 text-gray-400 fas fa-cog group-hover:text-orange-600 shrink-0"></i>
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