@php
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $homeUrl = auth()->check()
        ? ($isAdmin ? route('admin.index') : route('home'))
        : route('home');
@endphp

<nav x-data="{ open: false }" data-nav class="nav-bar sticky top-0 z-40 bg-paper text-brand-950">
    <div class="{{ request()->routeIs('home', 'habitaciones.show') ? 'wrap' : 'container-page' }}">
        <div class="flex h-[72px] items-center justify-between">

            {{-- Logo --}}
            <div class="flex items-center gap-8">
                <a href="{{ $homeUrl }}" class="group flex items-center gap-2.5 text-[1.0625rem] font-medium tracking-tight">
                    <svg class="h-6 w-6 text-brand-950 transition-transform duration-500 group-hover:-rotate-6" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor" d="M12 2 3 8v14h7v-6h4v6h7V8z"/>
                        <rect x="10.5" y="9" width="3" height="3" fill="#f5b301"/>
                    </svg>
                    {{ config('app.name', 'Hotel') }}
                </a>

                {{-- Desktop links --}}
                <div class="hidden items-center gap-1 md:flex">
                    @auth
                        @if ($isAdmin)
                            <x-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.index')">Panel</x-nav-link>
                            <x-nav-link :href="route('admin.zonas.index')" :active="request()->routeIs('admin.zonas.*')">Zonas</x-nav-link>
                            <x-nav-link :href="route('admin.habitaciones.index')" :active="request()->routeIs('admin.habitaciones.*')">Habitaciones</x-nav-link>
                            <x-nav-link :href="route('admin.reservas.index')" :active="request()->routeIs('admin.reservas.*')">Reservas</x-nav-link>
                            <x-nav-link :href="route('admin.usuarios.index')" :active="request()->routeIs('admin.usuarios.*')">Usuarios</x-nav-link>
                        @else
                            <x-nav-link :href="route('home')" :active="request()->routeIs('home')">Inicio</x-nav-link>
                            <x-nav-link :href="route('reservas.mias')" :active="request()->routeIs('reservas.mias')">Mis Reservas</x-nav-link>
                        @endif
                    @else
                        <x-nav-link :href="route('home')" :active="request()->routeIs('home')">Inicio</x-nav-link>
                    @endauth
                </div>
            </div>

            {{-- Right side --}}
            <div class="hidden items-center gap-3 md:flex">
                @auth
                    @if ($isAdmin)
                        @php
                            $noLeidas = \App\Models\Notificacion::where('user_id', Auth::id())->noLeidas()->count();
                        @endphp
                        <a href="{{ route('admin.notificaciones.index') }}"
                           class="relative grid h-11 w-11 place-items-center rounded-full text-brand-700 transition hover:bg-brand-100 hover:text-brand-950">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            @if ($noLeidas > 0)
                                <span class="absolute right-0.5 top-0.5 grid h-4 min-w-[1rem] place-items-center rounded-full bg-gold-400 px-1 text-[10px] font-bold text-brand-900">
                                    {{ $noLeidas > 9 ? '9+' : $noLeidas }}
                                </span>
                            @endif
                        </a>
                    @endif

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex min-h-[44px] items-center gap-2 rounded-full px-3 text-[0.9375rem] text-brand-800 transition hover:bg-brand-100">
                                <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-950 text-sm font-medium uppercase text-white">
                                    {{ \Illuminate\Support\Str::substr(Auth::user()->name, 0, 1) }}
                                </span>
                                <span class="max-w-[10rem] truncate">{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4 opacity-70" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Mi cuenta</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Cerrar sesión') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex min-h-[44px] items-center rounded-full px-4 text-[0.9375rem] text-brand-700 transition hover:bg-brand-100 hover:text-brand-950">
                        Iniciar sesión
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-dark btn-sm min-h-[44px] px-5 text-[0.9375rem]">
                        Crear cuenta
                    </a>
                @endauth
            </div>

            {{-- Hamburger --}}
            <div class="flex items-center md:hidden">
                <button @click="open = ! open" class="grid h-11 w-11 place-items-center rounded-full text-brand-950 transition hover:bg-brand-100" aria-label="Abrir menú">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden bg-paper md:hidden">
        <div class="container-page space-y-1 py-3">
            @auth
                @if ($isAdmin)
                    <x-responsive-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.index')">Panel</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.zonas.index')" :active="request()->routeIs('admin.zonas.*')">Zonas</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.habitaciones.index')" :active="request()->routeIs('admin.habitaciones.*')">Habitaciones</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.reservas.index')" :active="request()->routeIs('admin.reservas.*')">Reservas</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.usuarios.index')" :active="request()->routeIs('admin.usuarios.*')">Usuarios</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.notificaciones.index')" :active="request()->routeIs('admin.notificaciones.*')">Notificaciones</x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">Inicio</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reservas.mias')" :active="request()->routeIs('reservas.mias')">Mis Reservas</x-responsive-nav-link>
                @endif

                <div class="mt-3 rounded-[14px] bg-brand-100 p-3">
                    <div class="px-1 text-base font-medium text-brand-950">{{ Auth::user()->name }}</div>
                    <div class="px-1 text-sm text-brand-600">{{ Auth::user()->email }}</div>
                    <div class="mt-2 space-y-1">
                        <x-responsive-nav-link :href="route('profile.edit')">Mi cuenta</x-responsive-nav-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Cerrar sesión
                            </x-responsive-nav-link>
                        </form>
                    </div>
                </div>
            @else
                <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">Inicio</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('login')">Iniciar sesión</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')">Registrarse</x-responsive-nav-link>
            @endauth
        </div>
    </div>
</nav>
