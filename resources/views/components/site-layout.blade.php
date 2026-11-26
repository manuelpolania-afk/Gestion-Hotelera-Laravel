@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' — ' : '' }}{{ config('app.name', 'Hotel') }}</title>
    <link rel="preconnect" href="https://api.fontshare.com">
    <link rel="preconnect" href="https://cdn.fontshare.com" crossorigin>
    <script>document.documentElement.classList.add('js')</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper font-sans text-brand-950">
    <div class="flex min-h-screen flex-col">
        @include('layouts.navigation')

        <main class="flex-1">
            {{ $slot }}
        </main>

        {{-- Pie: el reverso de una postal --}}
        <footer class="bg-brand-950 pb-8 pt-24 text-brand-950 sm:pt-32">
            <div class="wrap">
                <div data-stagger class="postcard mx-auto max-w-[1180px] rounded-b-[14px] bg-paper px-6 pb-8 sm:px-12">
                    <div class="grid gap-12 md:grid-cols-2 md:gap-16">
                        <div>
                            <p class="text-[2rem] font-medium leading-tight tracking-[-0.03em]">Lo esperamos en casa.</p>
                            <p class="step-body mt-5 max-w-[36ch] text-brand-800">
                                Escríbanos para lo que necesite antes de llegar. La recepción atiende día y noche,
                                y su reserva queda guardada en su cuenta.
                            </p>
                            <p class="mt-8 text-[2.25rem] font-medium leading-none tracking-[-0.04em] sm:text-[3rem]">
                                {{ config('app.name', 'Hotel') }}
                            </p>
                        </div>

                        <div class="relative">
                            <figure class="absolute right-0 top-0 w-16 bg-white p-1.5 sm:w-[88px]" style="transform: rotate(3deg)">
                                <img src="{{ asset('images/ambiente/cafe.jpg') }}" alt="Tazas y tetera sobre una mesa de madera"
                                     width="88" height="117" loading="lazy" class="aspect-[3/4] w-full object-cover">
                            </figure>

                            <ul class="space-y-3 pt-24 text-lg sm:pt-28">
                                <li><a href="mailto:reservas@hotel.test" class="link-under">reservas@hotel.test</a></li>
                                <li>Recepción abierta las 24 horas</li>
                                <li><a href="{{ route('home') }}#habitaciones" class="link-under">Ver habitaciones libres</a></li>
                                @guest
                                    <li><a href="{{ route('login') }}" class="link-under">Iniciar sesión</a> o <a href="{{ route('register') }}" class="link-under">crear cuenta</a></li>
                                @else
                                    <li><a href="{{ route('reservas.mias') }}" class="link-under">Mis reservas</a></li>
                                @endguest
                            </ul>
                        </div>
                    </div>

                    <p class="step-small mx-auto mt-16 max-w-[70ch] text-center text-brand-600">
                        &copy; {{ date('Y') }} {{ config('app.name', 'Hotel') }}.
                        Fotografías de ambiente: Dominik Martin, David Mao, Logan Adermatt, Breather, Martin Wessely,
                        André Freitas y Luke Chesser, vía Unsplash (licencia Unsplash).
                    </p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
