<x-site-layout :title="$habitacion->nombre_habitacion">

    <div class="container-page py-10">

        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-500 hover:text-brand-800">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Volver a las habitaciones
        </a>

        <div class="mt-6 grid gap-8 lg:grid-cols-[1.4fr_1fr]">

            {{-- Columna izquierda: galería + info --}}
            <div class="space-y-6">
                <div class="card overflow-hidden">
                    @if ($habitacion->imagenes->isNotEmpty())
                        <div class="grid grid-cols-2 gap-1 sm:grid-cols-3">
                            @foreach ($habitacion->imagenes as $imagen)
                                <div class="{{ $loop->first ? 'col-span-2 sm:col-span-3' : '' }}">
                                    <img src="{{ asset('storage/' . $imagen->ruta) }}"
                                         alt="{{ $habitacion->nombre_habitacion }}"
                                         class="h-48 w-full object-cover {{ $loop->first ? 'sm:h-80' : '' }}">
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex h-64 flex-col items-center justify-center gap-2 bg-brand-100 text-brand-300">
                            <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                            <span class="text-sm">Sin imágenes disponibles</span>
                        </div>
                    @endif
                </div>

                <div class="card card-pad">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-blue">Zona {{ $habitacion->zona->codigo_zona }}</span>
                                @php
                                    $estadoBadge = match($habitacion->estado) {
                                        'Disponible' => 'badge-green',
                                        'Ocupada' => 'badge-red',
                                        'Mantenimiento' => 'badge-yellow',
                                        default => 'badge-gray',
                                    };
                                @endphp
                                <span class="badge {{ $estadoBadge }}">{{ $habitacion->estado }}</span>
                            </div>
                            <h1 class="mt-3 font-display text-3xl font-extrabold text-brand-900">
                                {{ $habitacion->nombre_habitacion }}
                            </h1>
                        </div>
                        <div class="text-right">
                            <p class="font-display text-3xl font-extrabold text-brand-900">
                                ${{ number_format($habitacion->valor, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-brand-400">por noche</p>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-4 border-t border-brand-100 pt-6 sm:grid-cols-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-400">Capacidad</p>
                            <p class="mt-1 font-display text-lg font-bold text-brand-800">{{ $habitacion->capacidad }} personas</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-400">Zona</p>
                            <p class="mt-1 font-display text-lg font-bold text-brand-800">{{ $habitacion->zona->codigo_zona }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-400">Estado</p>
                            <p class="mt-1 font-display text-lg font-bold text-brand-800">{{ $habitacion->estado }}</p>
                        </div>
                    </div>

                    @if ($habitacion->descripcion)
                        <div class="mt-6 border-t border-brand-100 pt-6">
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-400">Descripción</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-brand-600">{{ $habitacion->descripcion }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Columna derecha: reserva --}}
            <div class="lg:sticky lg:top-24 lg:self-start">
                <div class="card card-pad">
                    <h2 class="font-display text-lg font-bold text-brand-900">Reservar esta habitación</h2>
                    <p class="mt-1 text-sm text-brand-400">Completa los datos para confirmar tu estadía.</p>

                    <div class="mt-5 space-y-4">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if ($errors->has('mensaje'))
                            <div class="alert alert-error">{{ $errors->first('mensaje') }}</div>
                        @endif

                        @guest
                            <div class="rounded-xl bg-brand-50 p-5 text-center">
                                <p class="text-sm font-medium text-brand-700">
                                    Inicia sesión para reservar esta habitación.
                                </p>
                                <div class="mt-4 flex justify-center gap-3">
                                    <a href="{{ route('login') }}" class="btn btn-dark btn-sm">Iniciar sesión</a>
                                    <a href="{{ route('register') }}" class="btn btn-outline btn-sm">Registrarse</a>
                                </div>
                            </div>
                        @else
                            @if ($habitacion->estado !== 'Disponible')
                                <div class="alert alert-warning">
                                    Esta habitación no está disponible actualmente.
                                </div>
                            @else
                                <form method="POST" action="{{ route('reservas.store') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="habitacion_id" value="{{ $habitacion->id }}">

                                    <div>
                                        <label for="cantidad_personas" class="form-label">
                                            Cantidad de personas
                                            <span class="font-normal text-brand-400">(máx. {{ $habitacion->capacidad }})</span>
                                        </label>
                                        <input type="number" id="cantidad_personas" name="cantidad_personas"
                                               min="1" max="{{ $habitacion->capacidad }}"
                                               value="{{ old('cantidad_personas', 1) }}" required
                                               class="form-input">
                                        <x-input-error :messages="$errors->get('cantidad_personas')" class="mt-1" />
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="fecha_ingreso" class="form-label">Fecha de ingreso</label>
                                            <input type="date" id="fecha_ingreso" name="fecha_ingreso"
                                                   min="{{ now()->toDateString() }}"
                                                   value="{{ old('fecha_ingreso') }}" required
                                                   class="form-input">
                                            <x-input-error :messages="$errors->get('fecha_ingreso')" class="mt-1" />
                                        </div>
                                        <div>
                                            <label for="fecha_salida" class="form-label">Fecha de salida</label>
                                            <input type="date" id="fecha_salida" name="fecha_salida"
                                                   value="{{ old('fecha_salida') }}" required
                                                   class="form-input">
                                            <x-input-error :messages="$errors->get('fecha_salida')" class="mt-1" />
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-block">
                                        Confirmar reserva
                                    </button>
                                    <p class="text-center text-xs text-brand-400">No se realizará ningún cargo hasta confirmar en recepción.</p>
                                </form>
                            @endif
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-site-layout>
