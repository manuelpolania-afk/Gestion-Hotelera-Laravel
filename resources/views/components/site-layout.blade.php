@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' — ' : '' }}{{ config('app.name', 'Hotel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-50 font-sans text-brand-900">
    <div class="flex min-h-screen flex-col">
        @include('layouts.navigation')

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="mt-16 bg-brand-900 text-brand-200">
            <div class="container-page grid gap-8 py-12 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2 lg:col-span-2">
                    <div class="flex items-center gap-2.5 text-white">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-gold-400 text-brand-900">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75M3 3h12m-.75 4.5H21" /></svg>
                        </span>
                        <span class="font-display text-lg font-bold">{{ config('app.name', 'Hotel') }}</span>
                    </div>
                    <p class="mt-4 max-w-sm text-sm text-brand-300">
                        Habitaciones cómodas y bien ubicadas. Reserva en línea de forma rápida y segura.
                    </p>
                </div>
                <div>
                    <h3 class="font-display text-sm font-semibold uppercase tracking-wide text-white">Enlaces</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-white">Habitaciones</a></li>
                        @guest
                            <li><a href="{{ route('login') }}" class="hover:text-white">Iniciar sesión</a></li>
                            <li><a href="{{ route('register') }}" class="hover:text-white">Registrarse</a></li>
                        @else
                            <li><a href="{{ route('reservas.mias') }}" class="hover:text-white">Mis reservas</a></li>
                        @endguest
                    </ul>
                </div>
                <div>
                    <h3 class="font-display text-sm font-semibold uppercase tracking-wide text-white">Contacto</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li>Recepción 24 horas</li>
                        <li>reservas@hotel.test</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10">
                <div class="container-page py-5 text-center text-xs text-brand-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Hotel') }}. Todos los derechos reservados.
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
