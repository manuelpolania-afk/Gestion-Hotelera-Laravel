<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Hotel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-brand-900 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">

            {{-- Brand panel --}}
            <div class="relative hidden overflow-hidden bg-brand-900 p-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-gold-400/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-brand-500/30 blur-3xl"></div>

                <a href="{{ route('home') }}" class="relative flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-gold-400 text-brand-900">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21" />
                        </svg>
                    </span>
                    <span class="font-display text-xl font-bold">{{ config('app.name', 'Hotel') }}</span>
                </a>

                <div class="relative">
                    <h1 class="font-display text-4xl font-extrabold leading-tight">
                        Tu próxima estadía<br>empieza aquí.
                    </h1>
                    <p class="mt-4 max-w-sm text-brand-200">
                        Consulta disponibilidad, reserva tu habitación favorita y gestiona tus reservas en un solo lugar.
                    </p>
                </div>

                <p class="relative text-sm text-brand-300">&copy; {{ date('Y') }} {{ config('app.name', 'Hotel') }}</p>
            </div>

            {{-- Form panel --}}
            <div class="flex items-center justify-center bg-brand-50 px-6 py-12">
                <div class="w-full max-w-md">
                    <a href="{{ route('home') }}" class="mb-8 flex items-center justify-center gap-2 lg:hidden">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-900 text-gold-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M3.75 3v18m16.5-18v18M9 6.75h.75M9 12h.75m3.75-5.25h.75M13.5 12h.75" /></svg>
                        </span>
                        <span class="font-display text-lg font-bold text-brand-900">{{ config('app.name', 'Hotel') }}</span>
                    </a>

                    <div class="card card-pad sm:p-8">
                        {{ $slot }}
                    </div>

                    <p class="mt-6 text-center text-sm text-brand-400">
                        <a href="{{ route('home') }}" class="link">&larr; Volver al inicio</a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
