<x-site-layout title="Habitaciones disponibles">

    {{-- Hero --}}
    <section class="bg-brand-900 text-white">
        <div class="container-page relative overflow-hidden py-14 sm:py-20">
            <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-gold-400/20 blur-3xl"></div>
            <div class="relative max-w-2xl">
                <span class="badge badge-outline-green border-white/20 bg-white/10 text-gold-300">
                    Reserva en línea
                </span>
                <h1 class="mt-4 font-display text-4xl font-extrabold leading-tight sm:text-5xl">
                    Nuestras Habitaciones
                </h1>
                <p class="mt-4 text-lg text-brand-200">
                    Selecciona tu espacio preferido y consulta su disponibilidad.
                </p>
            </div>
        </div>
    </section>

    <div class="container-page pb-16">
        <div class="grid gap-6 lg:grid-cols-[16rem_1fr]">

            {{-- Sidebar de filtros --}}
            <aside class="-mt-10 lg:sticky lg:top-24 lg:self-start">
                <div class="card overflow-hidden">
                    <div class="flex items-center gap-3 bg-brand-900 px-5 py-4 text-white">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-gold-400 text-brand-900">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                        </span>
                        <div>
                            <p class="font-display text-sm font-bold">Filtrar por Zona</p>
                            <p class="text-xs text-brand-300">Ubicaciones del hotel</p>
                        </div>
                    </div>

                    <nav class="p-3">
                        <a href="{{ route('home') }}"
                           class="flex items-center justify-between rounded-xl border px-3 py-2.5 text-sm transition
                           {{ ! request('zona_id')
                               ? 'border-gold-300 bg-gold-50 font-semibold text-brand-900'
                               : 'border-transparent text-brand-600 hover:bg-brand-50' }}">
                            <span class="flex items-center gap-2">
                                <span class="grid h-8 w-8 place-items-center rounded-lg {{ ! request('zona_id') ? 'bg-gold-400 text-brand-900' : 'bg-brand-100 text-brand-500' }}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                                </span>
                                <span>
                                    <span class="block">Todas las Zonas</span>
                                    <span class="block text-xs font-normal text-brand-400">Catálogo completo</span>
                                </span>
                            </span>
                            @if (! request('zona_id'))
                                <svg class="h-4 w-4 text-gold-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                            @endif
                        </a>

                        @foreach ($zonas as $zona)
                            @php $active = request('zona_id') == $zona->id; @endphp
                            <a href="{{ route('home', ['zona_id' => $zona->id]) }}"
                               class="mt-1 flex items-center justify-between gap-2 rounded-xl border px-3 py-2.5 text-sm transition
                               {{ $active
                                   ? 'border-gold-300 bg-gold-50 font-semibold text-brand-900'
                                   : 'border-transparent text-brand-600 hover:bg-brand-50' }}">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg {{ $active ? 'bg-gold-400 text-brand-900' : 'bg-brand-100 text-brand-500' }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate">Zona {{ $zona->codigo_zona }}</span>
                                        <span class="block truncate text-xs font-normal text-brand-400">{{ $zona->descripcion ?: 'Sector '.$zona->codigo_zona }}</span>
                                    </span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 opacity-50" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
                            </a>
                        @endforeach

                        @if (request('zona_id'))
                            <a href="{{ route('home') }}" class="mt-3 block text-center text-xs font-medium text-red-500 hover:underline">
                                Limpiar filtro
                            </a>
                        @endif
                    </nav>
                </div>
            </aside>

            {{-- Grid de habitaciones --}}
            <div class="pt-2 lg:pt-6">
                <div class="mb-5 flex items-center justify-between">
                    <p class="text-sm text-brand-400">
                        {{ $habitaciones->count() }}
                        {{ $habitaciones->count() === 1 ? 'habitación' : 'habitaciones' }}
                        @if (request('zona_id')) en esta zona @endif
                    </p>
                    <span class="badge badge-blue">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 11.204 3.045c.44-.44 1.152-.44 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                        {{ $habitaciones->count() }} disponibles
                    </span>
                </div>

                @if ($habitaciones->isEmpty())
                    <div class="empty-state">
                        No hay habitaciones disponibles para esta zona.
                    </div>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($habitaciones as $habitacion)
                            <article class="card card-hover flex flex-col overflow-hidden">

                                <div class="relative">
                                    <span class="absolute left-3 top-3 z-10 inline-flex items-center gap-1 rounded-full bg-brand-900/80 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur">
                                        <svg class="h-3 w-3 text-gold-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933.5 12.9a.75.75 0 0 1 .82-1.257l3.36 2.203V4.5a.75.75 0 0 1 1.5 0v7.379l1.5-.984V2.75a.75.75 0 0 1 1.5 0v7.895l1.5-.984V4a.75.75 0 0 1 1.5 0v4.611l1.5-.983V6.25a.75.75 0 0 1 1.5 0v6.191l1.72-1.128a.75.75 0 0 1 .82 1.257l-9.19 6.033a.75.75 0 0 1-.82 0Z" clip-rule="evenodd" /></svg>
                                        {{ $habitacion->zona->codigo_zona }}
                                    </span>
                                    <span class="badge badge-outline-green absolute right-3 top-3 z-10 bg-white/90 uppercase tracking-wide backdrop-blur">
                                        {{ $habitacion->estado }}
                                    </span>

                                    @if ($habitacion->imagenPrincipal)
                                        <img src="{{ asset('storage/' . $habitacion->imagenPrincipal->ruta) }}"
                                             alt="{{ $habitacion->nombre_habitacion }}"
                                             class="h-52 w-full object-cover">
                                    @else
                                        <div class="flex h-52 w-full flex-col items-center justify-center gap-2 bg-brand-100 text-brand-300">
                                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                            <span class="text-sm">Sin fotografía disponible</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-1 flex-col p-5">
                                    <h3 class="font-display text-lg font-bold text-brand-900">
                                        {{ $habitacion->nombre_habitacion }}
                                    </h3>

                                    @if ($habitacion->descripcion)
                                        <p class="mt-1 line-clamp-2 text-sm text-brand-500">
                                            {{ $habitacion->descripcion }}
                                        </p>
                                    @endif

                                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-brand-500">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-4 w-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                                            Capacidad: <strong class="font-semibold text-brand-700">{{ $habitacion->capacidad }} pers.</strong>
                                        </span>
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-4 w-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                                            {{ $habitacion->zona->codigo_zona }}
                                        </span>
                                    </div>

                                    <div class="mt-4 flex items-end gap-1">
                                        <span class="font-display text-2xl font-extrabold text-brand-900">
                                            ${{ number_format($habitacion->valor, 0, ',', '.') }}
                                        </span>
                                        <span class="pb-1 text-xs text-brand-400">/ noche</span>
                                    </div>

                                    <a href="{{ route('habitaciones.show', $habitacion) }}"
                                       class="btn btn-primary btn-block mt-5">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                        Ver detalles y reservar
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-site-layout>
