<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'Hotel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-brand-50 font-sans">
        <div class="flex min-h-screen flex-col">
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-brand-100 bg-white">
                    <div class="container-page py-6">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="mt-auto border-t border-brand-100 bg-white">
                <div class="container-page flex flex-col items-center justify-between gap-2 py-6 text-sm text-brand-400 sm:flex-row">
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'Hotel') }}. Todos los derechos reservados.</p>
                    <p>Reservas y hospedaje</p>
                </div>
            </footer>
        </div>
    </body>
</html>
