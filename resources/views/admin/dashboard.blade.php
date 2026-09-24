<x-app-layout title="Panel de administración">
    <x-slot name="header">
        <div>
            <h2 class="page-title">Panel de Administración</h2>
            <p class="page-subtitle">Resumen general y accesos rápidos a la gestión del hotel.</p>
        </div>
    </x-slot>

    <div class="container-page py-8">

        @php
            $totalZonas = \App\Models\Zona::count();
            $totalHabitaciones = \App\Models\Habitacion::count();
            $disponibles = \App\Models\Habitacion::where('estado', 'Disponible')->count();
            $totalUsuarios = \App\Models\User::count();
            $reservasPendientes = \App\Models\Reserva::where('estado_reserva', 'Pendiente')->count();
            $reservasActivas = \App\Models\Reserva::whereIn('estado_reserva', ['Pendiente', 'Confirmada'])->count();
        @endphp

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Habitaciones', $totalHabitaciones, $disponibles.' disponibles', 'M2.25 12 11.2 3c.44-.44 1.15-.44 1.6 0L21.75 12M4.5 9.75v10.13c0 .62.5 1.12 1.13 1.12H9.75v-4.88c0-.62.5-1.12 1.13-1.12h2.25c.62 0 1.12.5 1.12 1.12V21h4.13c.62 0 1.12-.5 1.12-1.13V9.75'],
                ['Zonas', $totalZonas, 'Sectores del hotel', 'M9 6.75V15m6-6v8.25m.5 3.5 4.88-2.44c.38-.19.62-.58.62-1V4.82c0-.84-.88-1.38-1.63-1L15.5 5.75c-.32.16-.69.16-1 0L9.5 3.25c-.32-.16-.69-.16-1 0L3.62 5.69C3.24 5.88 3 6.27 3 6.7v12.48c0 .84.88 1.38 1.63 1l3.87-1.93c.32-.16.69-.16 1 0l4.99 2.5c.32.15.69.15 1 0'],
                ['Reservas activas', $reservasActivas, $reservasPendientes.' pendientes', 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5A2.25 2.25 0 0 1 5.25 5.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75'],
                ['Usuarios', $totalUsuarios, 'Registrados', 'M15 19.13a9.38 9.38 0 0 0 2.63.37 9.34 9.34 0 0 0 4.12-.95 4.13 4.13 0 0 0-7.53-2.5M15 19.13v-.11c0-1.11-.29-2.16-.79-3.07M15 19.13v.1A12.32 12.32 0 0 1 8.62 21c-2.33 0-4.51-.65-6.37-1.77v-.1a6.38 6.38 0 0 1 11.96-3.08M12 6.38a3.38 3.38 0 1 1-6.75 0 3.38 3.38 0 0 1 6.75 0Z'],
            ] as [$label, $value, $sub, $icon])
                <div class="card card-pad">
                    <div class="flex items-center justify-between">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-900 text-gold-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
                        </span>
                    </div>
                    <p class="mt-4 font-display text-3xl font-extrabold text-brand-900">{{ $value }}</p>
                    <p class="text-sm font-medium text-brand-600">{{ $label }}</p>
                    <p class="mt-0.5 text-xs text-brand-400">{{ $sub }}</p>
                </div>
            @endforeach
        </div>

        {{-- Módulos --}}
        <h3 class="mb-4 mt-10 font-display text-lg font-bold text-brand-900">Gestión</h3>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Zonas', 'Agrupa las habitaciones por sector del hotel.', route('admin.zonas.index'), 'Ver zonas'],
                ['Habitaciones', 'Administra habitaciones, imágenes, capacidad y estado.', route('admin.habitaciones.index'), 'Ver habitaciones'],
                ['Reservas', 'Consulta y actualiza el estado de las reservas.', route('admin.reservas.index'), 'Ver reservas'],
                ['Usuarios', 'Consulta los usuarios registrados y sus roles.', route('admin.usuarios.index'), 'Ver usuarios'],
            ] as [$title, $desc, $url, $cta])
                <div class="card card-pad flex flex-col">
                    <h4 class="font-display text-base font-bold text-brand-900">{{ $title }}</h4>
                    <p class="mt-1 flex-1 text-sm text-brand-500">{{ $desc }}</p>
                    <a href="{{ $url }}" class="btn btn-outline btn-block mt-4">{{ $cta }}</a>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
