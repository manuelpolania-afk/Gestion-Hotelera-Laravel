@php
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $homeUrl = auth()->check()
        ? ($isAdmin ? route('admin.index') : route('home'))
        : route('home');
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-brand-900 text-white shadow-lg shadow-brand-900/10">
    <div class="container-page">
        <div class="flex h-16 items-center justify-between">

            {{-- Logo --}}
            <div class="flex items-center gap-8">
                <a href="{{ $homeUrl }}" class="flex items-center gap-2.5">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-gold-400 text-brand-900">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                        </svg>
                    </span>
                    <span class="font-display text-lg font-bold tracking-tight">
                        {{ config('app.name', 'Hotel') }}
                    </span>
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
                           class="relative rounded-lg p-2 text-brand-200 transition hover:bg-white/10 hover:text-white">
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
                            <button class="flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-sm font-medium text-brand-100 transition hover:bg-white/10">
                                <span class="grid h-7 w-7 place-items-center rounded-full bg-white/10 text-xs font-bold uppercase text-white">
                                    {{ \Illuminate\Support\Str::substr(Auth::user()->name, 0, 1) }}
                                </span>
                                <span class="max-w-[10rem] truncate">{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4 opacity-70" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Perfil') }}</x-dropdown-link>
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
                       class="inline-flex items-center gap-1.5 rounded-xl border border-white/20 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" /></svg>
                        Iniciar sesión
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                        Registrarse
                    </a>
                @endauth
            </div>

            {{-- Hamburger --}}
            <div class="flex items-center md:hidden">
                <button @click="open = ! open" class="rounded-lg p-2 text-brand-100 transition hover:bg-white/10">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-white/10 bg-brand-900 md:hidden">
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

                <div class="mt-3 border-t border-white/10 pt-3">
                    <div class="px-3 text-sm font-semibold text-white">{{ Auth::user()->name }}</div>
                    <div class="px-3 text-xs text-brand-300">{{ Auth::user()->email }}</div>
                    <div class="mt-2 space-y-1">
                        <x-responsive-nav-link :href="route('profile.edit')">Perfil</x-responsive-nav-link>
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
